<?php

declare(strict_types=1);

namespace Modules\ReferenceCatalog\Application;

use Base\Platform\Media\Public\Contracts\MediaSynchronizer;
use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Base\Platform\Media\Public\ValueObjects\MediaOwnerReference;
use Base\Platform\Media\Public\ValueObjects\MediaReference;
use Base\Platform\Media\Public\ValueObjects\MediaSlotChange;
use Base\Platform\Media\Public\ValueObjects\MediaSlotChanges;
use Base\Platform\Media\Public\ValueObjects\MediaSlotDefinition;
use Illuminate\Support\Facades\DB;
use Modules\ReferenceCatalog\Infrastructure\Models\ReferenceItem;

final class ReferenceItemCreator
{
    public function __construct(
        private readonly MediaSynchronizer $mediaSynchronizer
    ) {}

    /**
     * @param  list<MediaReference>|null  $gallery
     */
    public function create(string $name, MediaAccessScope $scope, ?MediaReference $cover = null, ?array $gallery = null): ReferenceItem
    {
        return DB::transaction(function () use ($name, $scope, $cover, $gallery) {
            $item = ReferenceItem::create(['name' => $name]);

            $owner = new MediaOwnerReference('reference-catalog.item', (string) $item->id);

            $changes = [];
            if ($cover !== null) {
                $changes['cover'] = MediaSlotChange::set($cover);
            }
            if ($gallery !== null) {
                $changes['gallery'] = MediaSlotChange::set($gallery);
            }

            if (! empty($changes)) {
                $this->mediaSynchronizer->sync(
                    $owner,
                    $scope,
                    new MediaSlotChanges($changes),
                    [
                        MediaSlotDefinition::single('cover'),
                        MediaSlotDefinition::multiple('gallery'),
                    ]
                );
            }

            return $item;
        });
    }
}
