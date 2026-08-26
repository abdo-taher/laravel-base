<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Platform\Media;

use Base\Platform\Files\Public\Contracts\FileStorage;
use Base\Platform\Media\Infrastructure\Database\MediaItem;
use Base\Platform\Media\MediaServiceProvider;
use Base\Platform\Media\Public\Contracts\MediaSynchronizer;
use Base\Platform\Media\Public\Exceptions\MediaSlotViolation;
use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Base\Platform\Media\Public\ValueObjects\MediaOwnerReference;
use Base\Platform\Media\Public\ValueObjects\MediaReference;
use Base\Platform\Media\Public\ValueObjects\MediaSlotChange;
use Base\Platform\Media\Public\ValueObjects\MediaSlotChanges;
use Base\Platform\Media\Public\ValueObjects\MediaSlotDefinition;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class MediaHttpUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(MediaServiceProvider::class);
        $this->artisan('migrate');

        // Mock FileStorage as Media needs it
        $storageMock = $this->createMock(FileStorage::class);
        $storageMock->method('write');
        $this->app->instance(FileStorage::class, $storageMock);
    }

    public function test_upload_success_and_sync_integration(): void
    {
        $user = new User;
        $user->id = 123;

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)
            ->postJson('/api/media', [
                'file' => $file,
            ]);

        if ($response->status() !== 201) {
            $response->dump();
        }
        $response->assertStatus(201);
        $response->assertJsonStructure(['reference']);

        $reference = $response->json('reference');
        $this->assertStringStartsWith('med_', $reference);

        $media = MediaItem::where('reference', $reference)->first();
        $this->assertNotNull($media);
        $this->assertEquals('TEMPORARY', $media->state);
        $this->assertEquals('principal:123', $media->upload_scope);

        // Prove sync works with the same scope
        $synchronizer = $this->app->make(MediaSynchronizer::class);
        $scope = MediaAccessScope::fromString('principal:123');
        $owner = new MediaOwnerReference('test_product', 'prod_1');

        $synchronizer->sync(
            $owner,
            $scope,
            new MediaSlotChanges([
                'avatar' => MediaSlotChange::set(
                    MediaReference::fromString($reference)
                ),
            ]),
            [
                MediaSlotDefinition::single('avatar'),
            ]
        );

        $media->refresh();
        $this->assertEquals('ATTACHED', $media->state);
    }

    public function test_upload_fails_for_guest(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/media', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_upload_fails_without_file(): void
    {
        $user = new User;
        $user->id = 123;

        $response = $this->actingAs($user)
            ->postJson('/api/media', []);

        $response->assertStatus(422);
    }

    public function test_sync_fails_with_different_scope(): void
    {
        $user = new User;
        $user->id = 123;

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)
            ->postJson('/api/media', [
                'file' => $file,
            ]);

        $reference = $response->json('reference');

        $synchronizer = $this->app->make(MediaSynchronizer::class);
        $differentScope = MediaAccessScope::fromString('principal:999');
        $owner = new MediaOwnerReference('test_product', 'prod_1');

        $this->expectException(MediaSlotViolation::class);

        $synchronizer->sync(
            $owner,
            $differentScope,
            new MediaSlotChanges([
                'avatar' => MediaSlotChange::set(
                    MediaReference::fromString($reference)
                ),
            ]),
            [
                MediaSlotDefinition::single('avatar'),
            ]
        );
    }
}
