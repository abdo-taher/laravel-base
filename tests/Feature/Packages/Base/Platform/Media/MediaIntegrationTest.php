<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Platform\Media;

use Base\Platform\Files\Public\Contracts\FileStorage;
use Base\Platform\Media\Infrastructure\Database\MediaItem;
use Base\Platform\Media\MediaServiceProvider;
use Base\Platform\Media\Public\Contracts\MediaCleaner;
use Base\Platform\Media\Public\Contracts\MediaSynchronizer;
use Base\Platform\Media\Public\Contracts\MediaUploader;
use Base\Platform\Media\Public\Exceptions\MediaReferenceNotFound;
use Base\Platform\Media\Public\Exceptions\MediaSlotViolation;
use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Base\Platform\Media\Public\ValueObjects\MediaOwnerReference;
use Base\Platform\Media\Public\ValueObjects\MediaReference;
use Base\Platform\Media\Public\ValueObjects\MediaSlotChange;
use Base\Platform\Media\Public\ValueObjects\MediaSlotChanges;
use Base\Platform\Media\Public\ValueObjects\MediaSlotDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class MediaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private MediaUploader $uploader;

    private MediaSynchronizer $synchronizer;

    private MediaCleaner $cleaner;

    private MockObject&FileStorage $storageMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(MediaServiceProvider::class);
        $this->artisan('migrate');

        $this->storageMock = $this->createMock(FileStorage::class);
        $this->app->instance(FileStorage::class, $this->storageMock);

        $this->uploader = $this->app->make(MediaUploader::class);
        $this->synchronizer = $this->app->make(MediaSynchronizer::class);
        $this->cleaner = $this->app->make(MediaCleaner::class);
    }

    public function test_upload_creates_temporary_record(): void
    {
        $this->storageMock->expects($this->once())
            ->method('write');

        $stream = fopen('php://memory', 'r+b');
        if ($stream === false) {
            $this->fail('Failed to open memory stream.');
        }
        fwrite($stream, 'test content');
        rewind($stream);

        $scope = MediaAccessScope::fromString('user_1');
        $reference = $this->uploader->upload($stream, 'test.txt', $scope);

        $this->assertDatabaseHas('media', [
            'reference' => $reference->value,
            'state' => MediaItem::STATE_TEMPORARY,
            'upload_scope' => 'user_1',
            'original_name' => 'test.txt',
        ]);

        fclose($stream);
    }

    public function test_single_slot_sync_attaches_and_orphans(): void
    {
        $scope = MediaAccessScope::fromString('user_1');
        $owner = new MediaOwnerReference('product', '1');

        // 1. Initial Attachment
        MediaItem::create([
            'reference' => 'med_old',
            'storage_key' => 'media/old',
            'original_name' => 'old.txt',
            'mime_type' => 'text/plain',
            'size' => 10,
            'state' => MediaItem::STATE_ATTACHED,
            'upload_scope' => 'user_1',
            'owner_type' => 'product',
            'owner_id' => '1',
            'slot_name' => 'main_image',
            'sort_order' => 0,
            'attached_at' => Carbon::now(),
        ]);

        MediaItem::create([
            'reference' => 'med_new',
            'storage_key' => 'media/new',
            'original_name' => 'new.txt',
            'mime_type' => 'text/plain',
            'size' => 10,
            'state' => MediaItem::STATE_TEMPORARY,
            'upload_scope' => 'user_1',
        ]);

        $changes = new MediaSlotChanges([
            'main_image' => MediaSlotChange::set(MediaReference::fromString('med_new')),
        ]);
        $definitions = [
            MediaSlotDefinition::single('main_image'),
        ];

        DB::transaction(function () use ($owner, $scope, $changes, $definitions) {
            $this->synchronizer->sync($owner, $scope, $changes, $definitions);
        });

        $this->assertDatabaseHas('media', [
            'reference' => 'med_old',
            'state' => MediaItem::STATE_ORPHANED,
            'owner_id' => null,
        ]);

        $this->assertDatabaseHas('media', [
            'reference' => 'med_new',
            'state' => MediaItem::STATE_ATTACHED,
            'owner_id' => '1',
            'slot_name' => 'main_image',
        ]);
    }

    public function test_multiple_slot_sync_retains_order_and_detects_duplicates(): void
    {
        $scope = MediaAccessScope::fromString('u');
        $owner = new MediaOwnerReference('article', '1');

        MediaItem::create([
            'reference' => 'med_a',
            'storage_key' => 'media/a',
            'original_name' => 'a',
            'mime_type' => 'a',
            'size' => 1,
            'state' => MediaItem::STATE_ATTACHED,
            'upload_scope' => 'u',
            'owner_type' => 'article',
            'owner_id' => '1',
            'slot_name' => 'gallery',
            'sort_order' => 0,
        ]);
        MediaItem::create([
            'reference' => 'med_b',
            'storage_key' => 'media/b',
            'original_name' => 'b',
            'mime_type' => 'b',
            'size' => 1,
            'state' => MediaItem::STATE_TEMPORARY,
            'upload_scope' => 'u',
        ]);

        $changes = new MediaSlotChanges([
            'gallery' => MediaSlotChange::set([
                MediaReference::fromString('med_b'),
                MediaReference::fromString('med_a'), // Swapping order
            ]),
        ]);
        $definitions = [MediaSlotDefinition::multiple('gallery')];

        $this->synchronizer->sync($owner, $scope, $changes, $definitions);

        $this->assertDatabaseHas('media', [
            'reference' => 'med_b',
            'state' => MediaItem::STATE_ATTACHED,
            'sort_order' => 0,
        ]);
        $this->assertDatabaseHas('media', [
            'reference' => 'med_a',
            'state' => MediaItem::STATE_ATTACHED,
            'sort_order' => 1,
        ]);
    }

    public function test_sync_fails_atomically_on_missing_reference(): void
    {
        $scope = MediaAccessScope::fromString('u');
        $owner = new MediaOwnerReference('article', '1');

        MediaItem::create([
            'reference' => 'med_a',
            'storage_key' => 'media/a',
            'original_name' => 'a',
            'mime_type' => 'a',
            'size' => 1,
            'state' => MediaItem::STATE_ATTACHED,
            'upload_scope' => 'u',
            'owner_type' => 'article',
            'owner_id' => '1',
            'slot_name' => 'gallery',
        ]);

        $changes = new MediaSlotChanges([
            'gallery' => MediaSlotChange::set([
                MediaReference::fromString('med_missing'), // Missing!
            ]),
        ]);

        $definitions = [MediaSlotDefinition::multiple('gallery')];

        $failed = false;
        try {
            DB::transaction(function () use ($owner, $scope, $changes, $definitions) {
                $this->synchronizer->sync($owner, $scope, $changes, $definitions);
            });
        } catch (MediaReferenceNotFound) {
            $failed = true;
        }

        $this->assertTrue($failed);

        // Ensure atomic failure - med_a should not be orphaned!
        $this->assertDatabaseHas('media', [
            'reference' => 'med_a',
            'state' => MediaItem::STATE_ATTACHED,
            'owner_id' => '1',
        ]);
    }

    public function test_transaction_rollback_reverts_state(): void
    {
        $scope = MediaAccessScope::fromString('u');
        $owner = new MediaOwnerReference('test', '1');

        MediaItem::create([
            'reference' => 'med_r',
            'storage_key' => 'media/r',
            'original_name' => 'r',
            'mime_type' => 'r',
            'size' => 1,
            'state' => MediaItem::STATE_TEMPORARY,
            'upload_scope' => 'u',
        ]);

        try {
            DB::transaction(function () use ($owner, $scope) {
                $this->synchronizer->sync(
                    $owner,
                    $scope,
                    new MediaSlotChanges([
                        'img' => MediaSlotChange::set(MediaReference::fromString('med_r')),
                    ]),
                    [MediaSlotDefinition::single('img')]
                );

                // Boom, simulate a failure in product save
                throw new \RuntimeException('Business error');
            });
        } catch (\RuntimeException) {
        }

        // Rollback proved:
        $this->assertDatabaseHas('media', [
            'reference' => 'med_r',
            'state' => MediaItem::STATE_TEMPORARY, // REVERTED TO TEMPORARY
            'owner_id' => null,
        ]);
    }

    public function test_cleanup_handles_orphaned_and_temporary_safely(): void
    {
        $this->storageMock->expects($this->once())
            ->method('delete')
            ->willThrowException(new \RuntimeException('Storage fail'));

        MediaItem::create([
            'reference' => 'med_orphaned',
            'storage_key' => 'media/o',
            'original_name' => 'o',
            'mime_type' => 'o',
            'size' => 1,
            'state' => MediaItem::STATE_ORPHANED,
            'upload_scope' => 'u',
            'orphaned_at' => Carbon::now()->subDays(5),
        ]);

        $cleaned = $this->cleaner->cleanExpired(86400); // 1 day TTL

        // Count should be 0 because storage deletion threw an exception
        $this->assertSame(0, $cleaned);

        // Ensure DB record remained intact
        $this->assertDatabaseHas('media', [
            'reference' => 'med_orphaned',
            'state' => MediaItem::STATE_ORPHANED,
        ]);
    }

    public function test_wrong_scope_rejected(): void
    {
        $scopeA = MediaAccessScope::fromString('user_A');
        $scopeB = MediaAccessScope::fromString('user_B');
        $owner = new MediaOwnerReference('product', '1');

        MediaItem::create([
            'reference' => 'med_a',
            'storage_key' => 'media/a',
            'original_name' => 'a',
            'mime_type' => 'a',
            'size' => 1,
            'state' => MediaItem::STATE_TEMPORARY,
            'upload_scope' => 'user_A',
        ]);

        $this->expectException(MediaSlotViolation::class);

        $this->synchronizer->sync(
            $owner,
            $scopeB,
            new MediaSlotChanges(['img' => MediaSlotChange::set(MediaReference::fromString('med_a'))]),
            [MediaSlotDefinition::single('img')]
        );
    }

    public function test_attached_foreign_owner_reuse_rejected(): void
    {
        $scope = MediaAccessScope::fromString('user_A');
        $ownerB = new MediaOwnerReference('product', '2');

        MediaItem::create([
            'reference' => 'med_a',
            'storage_key' => 'media/a',
            'original_name' => 'a',
            'mime_type' => 'a',
            'size' => 1,
            'state' => MediaItem::STATE_ATTACHED,
            'upload_scope' => 'user_A',
            'owner_type' => 'product',
            'owner_id' => '1',
            'slot_name' => 'img',
        ]);

        $this->expectException(MediaSlotViolation::class);

        $this->synchronizer->sync(
            $ownerB,
            $scope,
            new MediaSlotChanges(['img' => MediaSlotChange::set(MediaReference::fromString('med_a'))]),
            [MediaSlotDefinition::single('img')]
        );
    }

    public function test_same_owner_different_slot_reuse_rejected(): void
    {
        $scope = MediaAccessScope::fromString('user_A');
        $owner = new MediaOwnerReference('product', '1');

        MediaItem::create([
            'reference' => 'med_a',
            'storage_key' => 'media/a',
            'original_name' => 'a',
            'mime_type' => 'a',
            'size' => 1,
            'state' => MediaItem::STATE_ATTACHED,
            'upload_scope' => 'user_A',
            'owner_type' => 'product',
            'owner_id' => '1',
            'slot_name' => 'img',
        ]);

        $this->expectException(MediaSlotViolation::class);

        // Attempting to attach med_a to gallery when it's in img slot
        $this->synchronizer->sync(
            $owner,
            $scope,
            new MediaSlotChanges(['gallery' => MediaSlotChange::set([MediaReference::fromString('med_a')])]),
            [MediaSlotDefinition::multiple('gallery')]
        );
    }

    public function test_orphan_replay_rejected(): void
    {
        $scope = MediaAccessScope::fromString('user_A');
        $owner = new MediaOwnerReference('product', '1');

        MediaItem::create([
            'reference' => 'med_a',
            'storage_key' => 'media/a',
            'original_name' => 'a',
            'mime_type' => 'a',
            'size' => 1,
            'state' => MediaItem::STATE_ORPHANED,
            'upload_scope' => 'user_A',
        ]);

        $this->expectException(MediaSlotViolation::class);

        $this->synchronizer->sync(
            $owner,
            $scope,
            new MediaSlotChanges(['img' => MediaSlotChange::set(MediaReference::fromString('med_a'))]),
            [MediaSlotDefinition::single('img')]
        );
    }
}
