<?php

declare(strict_types=1);

namespace Base\Platform\Media\Application;

use Base\Platform\Files\Public\Contracts\FileStorage;
use Base\Platform\Files\Public\ValueObjects\StorageKey;
use Base\Platform\Media\Infrastructure\Database\MediaItem;
use Base\Platform\Media\Public\Contracts\MediaCleaner;
use Base\Platform\Media\Public\Contracts\MediaSynchronizer;
use Base\Platform\Media\Public\Contracts\MediaUploader;
use Base\Platform\Media\Public\Exceptions\MediaReferenceNotFound;
use Base\Platform\Media\Public\Exceptions\MediaSlotViolation;
use Base\Platform\Media\Public\Exceptions\MediaUploadFailed;
use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Base\Platform\Media\Public\ValueObjects\MediaOwnerReference;
use Base\Platform\Media\Public\ValueObjects\MediaReference;
use Base\Platform\Media\Public\ValueObjects\MediaSlotChanges;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

final class MediaApplicationService implements MediaCleaner, MediaSynchronizer, MediaUploader
{
    public function __construct(private readonly FileStorage $storage) {}

    public function upload(mixed $stream, string $originalName, MediaAccessScope $scope): MediaReference
    {
        try {
            $mimeType = 'application/octet-stream';
            $size = 0;

            $safeOriginalName = basename($originalName);

            if (is_resource($stream)) {
                $stat = fstat($stream);
                $size = $stat['size'] ?? 0;

                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo !== false) {
                        $meta = stream_get_meta_data($stream);
                        if (isset($meta['uri']) && file_exists($meta['uri']) && $meta['uri'] !== 'php://temp' && $meta['uri'] !== 'php://memory') {
                            $detectedMime = finfo_file($finfo, $meta['uri']);
                            if ($detectedMime !== false) {
                                $mimeType = $detectedMime;
                            }
                        } else {
                            $position = ftell($stream);
                            if ($position !== false) {
                                rewind($stream);
                                $content = stream_get_contents($stream, 4096);
                                if ($content !== false) {
                                    $detectedMime = finfo_buffer($finfo, $content);
                                    if ($detectedMime !== false) {
                                        $mimeType = $detectedMime;
                                    }
                                }
                                fseek($stream, $position);
                            }
                        }
                        finfo_close($finfo);
                    }
                }
            }

            $entropy = bin2hex(random_bytes(16));
            $reference = 'med_'.strtolower($entropy);
            $storageKeyStr = 'media/'.$entropy;
            $storageKey = new StorageKey($storageKeyStr);

            $this->storage->write($storageKey, $stream);
        } catch (Throwable $e) {
            throw MediaUploadFailed::transport($e);
        }

        try {
            MediaItem::create([
                'reference' => $reference,
                'storage_key' => $storageKeyStr,
                'original_name' => $safeOriginalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'state' => MediaItem::STATE_TEMPORARY,
                'upload_scope' => $scope->value,
            ]);

            return MediaReference::fromString($reference);
        } catch (Throwable $e) {
            try {
                $this->storage->delete($storageKey);
            } catch (Throwable) {
            }
            throw MediaUploadFailed::transport($e);
        }
    }

    public function sync(MediaOwnerReference $owner, MediaAccessScope $scope, MediaSlotChanges $changes, array $slotDefinitions): void
    {
        DB::transaction(function () use ($owner, $scope, $changes, $slotDefinitions) {
            foreach ($slotDefinitions as $definition) {
                $change = $changes->getChangeForSlot($definition->name);

                if ($change->isUntouched()) {
                    continue;
                }

                // Existing attachments - explicitly ordered to prevent deadlocks
                $existing = MediaItem::where('owner_type', $owner->type)
                    ->where('owner_id', $owner->id)
                    ->where('slot_name', $definition->name->value)
                    ->where('state', MediaItem::STATE_ATTACHED)
                    ->orderBy('reference')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('reference');

                if ($change->isClear()) {
                    foreach ($existing as $item) {
                        $item->update([
                            'state' => MediaItem::STATE_ORPHANED,
                            'owner_type' => null,
                            'owner_id' => null,
                            'slot_name' => null,
                            'sort_order' => null,
                            'orphaned_at' => Carbon::now(),
                        ]);
                    }

                    continue;
                }

                $requestedRefs = array_map(fn (MediaReference $r) => $r->value, $change->references ?? []);

                if (count($requestedRefs) !== count(array_unique($requestedRefs))) {
                    throw MediaSlotViolation::duplicateReference('Duplicate reference detected in payload.');
                }

                if ($definition->isMultiple && $definition->maxItems !== null && count($requestedRefs) > $definition->maxItems) {
                    throw MediaSlotViolation::invalidCardinality($definition->name->value, "Exceeds max items {$definition->maxItems}");
                }
                if (! $definition->isMultiple && count($requestedRefs) > 1) {
                    throw MediaSlotViolation::invalidCardinality($definition->name->value, 'Single slot cannot accept multiple references.');
                }

                // Incoming references - explicitly ordered to prevent deadlocks
                $incoming = MediaItem::whereIn('reference', $requestedRefs)
                    ->orderBy('reference')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('reference');

                // PREVALIDATION PHASE
                foreach ($requestedRefs as $refStr) {
                    $item = $incoming->get($refStr);

                    if ($item === null) {
                        throw MediaReferenceNotFound::fromString($refStr);
                    }

                    if ($existing->has($refStr)) {
                        continue;
                    }

                    if ($item->state !== MediaItem::STATE_TEMPORARY) {
                        throw MediaSlotViolation::scopeMismatch($refStr);
                    }

                    if ($item->upload_scope !== $scope->value) {
                        throw MediaSlotViolation::scopeMismatch($refStr);
                    }

                    if (! empty($definition->allowedMimeTypes) && ! in_array($item->mime_type, $definition->allowedMimeTypes, true)) {
                        throw MediaSlotViolation::invalidMime($refStr, implode(', ', $definition->allowedMimeTypes));
                    }

                    if ($definition->maxSizeBytes !== null && $item->size > $definition->maxSizeBytes) {
                        throw MediaSlotViolation::invalidSize($refStr, $definition->maxSizeBytes);
                    }
                }

                // MUTATION PHASE
                foreach ($existing as $refStr => $item) {
                    if (! in_array($refStr, $requestedRefs, true)) {
                        $item->update([
                            'state' => MediaItem::STATE_ORPHANED,
                            'owner_type' => null,
                            'owner_id' => null,
                            'slot_name' => null,
                            'sort_order' => null,
                            'orphaned_at' => Carbon::now(),
                        ]);
                    }
                }

                $order = 0;
                foreach ($requestedRefs as $refStr) {
                    $item = $incoming->get($refStr);
                    if ($item === null) {
                        continue;
                    }
                    $item->update([
                        'state' => MediaItem::STATE_ATTACHED,
                        'owner_type' => $owner->type,
                        'owner_id' => $owner->id,
                        'slot_name' => $definition->name->value,
                        'sort_order' => $order++,
                        'attached_at' => Carbon::now(),
                    ]);
                }
            }
        });
    }

    public function cleanExpired(int $ttlSeconds): int
    {
        $threshold = Carbon::now()->subSeconds($ttlSeconds);

        $orphaned = MediaItem::where('state', MediaItem::STATE_ORPHANED)
            ->where('orphaned_at', '<', $threshold)
            ->get();

        $temporary = MediaItem::where('state', MediaItem::STATE_TEMPORARY)
            ->where('created_at', '<', $threshold)
            ->get();

        $count = 0;
        foreach ($orphaned->concat($temporary) as $item) {
            try {
                $this->storage->delete(new StorageKey($item->storage_key));
                $item->delete();
                $count++;
            } catch (Throwable) {
                // Ignore if storage delete fails
            }
        }

        return $count;
    }
}
