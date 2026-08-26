<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\ReferenceCatalog;

use Base\Platform\Files\FilesServiceProvider;
use Base\Platform\Media\Infrastructure\Database\MediaItem;
use Base\Platform\Media\MediaServiceProvider;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Modules\ReferenceCatalog\ReferenceCatalogServiceProvider;
use Tests\TestCase;

final class ReferenceCatalogE2ETest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, class-string<ServiceProvider>> */
    protected function getPackageProviders(mixed $app): array
    {
        return [
            FilesServiceProvider::class,
            MediaServiceProvider::class,
            ReferenceCatalogServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(FilesServiceProvider::class);
        $this->app->register(MediaServiceProvider::class);
        $this->app->register(ReferenceCatalogServiceProvider::class);

        $this->artisan('migrate');
        Storage::fake('local');
    }

    private function uploadMedia(): string
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $response = $this->postJson('/api/media', [
            'file' => $file,
        ]);

        $response->assertStatus(201);

        $ref = $response->json('reference');

        return is_string($ref) ? $ref : '';
    }

    public function test_end_to_end_media_upload_and_product_create(): void
    {
        // Mock Auth
        $user = new class extends User
        {
            protected $table = 'users'; // mock table just to bypass auth

            public function getAuthIdentifier()
            {
                return 1;
            }
        };
        $this->actingAs($user);

        // Upload media
        $coverRef = $this->uploadMedia();
        $galleryRef1 = $this->uploadMedia();
        $galleryRef2 = $this->uploadMedia();

        // Create product
        $response = $this->postJson('/api/reference-items', [
            'name' => 'Architecture Proof',
            'cover' => $coverRef,
            'gallery' => [$galleryRef1, $galleryRef2],
        ]);

        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertNotNull($id);

        $dbItem = DB::table('reference_items')->where('id', $id)->first();
        $this->assertNotNull($dbItem);
        $this->assertSame('Architecture Proof', $dbItem->name);

        // Assert cover ATTACHED
        $coverMedia = MediaItem::where('reference', $coverRef)->first();
        $this->assertNotNull($coverMedia);
        $this->assertSame('ATTACHED', $coverMedia->state);
        $this->assertSame('reference-catalog.item', $coverMedia->owner_type);
        $this->assertSame($id, $coverMedia->owner_id);
        $this->assertSame('cover', $coverMedia->slot_name);
        $this->assertSame(0, $coverMedia->sort_order);

        // Assert gallery ATTACHED
        $galleryMedia1 = MediaItem::where('reference', $galleryRef1)->first();
        $this->assertNotNull($galleryMedia1);
        $this->assertSame('ATTACHED', $galleryMedia1->state);
        $this->assertSame('reference-catalog.item', $galleryMedia1->owner_type);
        $this->assertSame($id, $galleryMedia1->owner_id);
        $this->assertSame('gallery', $galleryMedia1->slot_name);
        $this->assertSame(0, $galleryMedia1->sort_order);

        $galleryMedia2 = MediaItem::where('reference', $galleryRef2)->first();
        $this->assertNotNull($galleryMedia2);
        $this->assertSame('ATTACHED', $galleryMedia2->state);
        $this->assertSame('gallery', $galleryMedia2->slot_name);
        $this->assertSame(1, $galleryMedia2->sort_order);
    }

    public function test_wrong_scope_rollback_proof(): void
    {
        $userA = new class extends User
        {
            public function getAuthIdentifier()
            {
                return 1;
            }
        };

        $this->actingAs($userA);
        $coverRef = $this->uploadMedia();

        $userB = new class extends User
        {
            public function getAuthIdentifier()
            {
                return 2;
            }
        };
        $this->actingAs($userB);

        $response = $this->postJson('/api/reference-items', [
            'name' => 'Architecture Proof',
            'cover' => $coverRef,
        ]);

        $response->assertStatus(422);

        $this->assertSame(0, DB::table('reference_items')->count());

        $coverMedia = MediaItem::where('reference', $coverRef)->first();
        $this->assertNotNull($coverMedia);
        $this->assertSame('TEMPORARY', $coverMedia->state);
    }

    public function test_missing_media_rollback_proof(): void
    {
        $user = new class extends User
        {
            public function getAuthIdentifier()
            {
                return 1;
            }
        };
        $this->actingAs($user);

        $response = $this->postJson('/api/reference-items', [
            'name' => 'Architecture Proof',
            'cover' => 'med_deadbeef1234',
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, DB::table('reference_items')->count());
    }
}
