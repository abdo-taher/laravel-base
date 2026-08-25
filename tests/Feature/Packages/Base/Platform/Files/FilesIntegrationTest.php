<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Platform\Files;

use Base\Platform\Files\FilesServiceProvider;
use Base\Platform\Files\Public\Contracts\FileStorage;
use Base\Platform\Files\Public\Exceptions\FileNotFound;
use Base\Platform\Files\Public\ValueObjects\StorageKey;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class FilesIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(FilesServiceProvider::class);

        // Use a temporary fake local disk for the test
        Storage::fake('local');
    }

    public function test_write_read_exists_delete_missing_flow(): void
    {
        /** @var FileStorage $storage */
        $storage = $this->app->make(FileStorage::class);

        $key = new StorageKey('integration/test.txt');

        // Missing
        $this->assertFalse($storage->exists($key));

        try {
            $storage->read($key);
            $this->fail('Expected FileNotFound exception');
        } catch (FileNotFound $e) {
            // Expected
        }

        // Write
        $storage->write($key, 'hello world');
        $this->assertTrue($storage->exists($key));

        // Read
        $content = $storage->read($key);
        $this->assertSame('hello world', $content);

        // Read stream
        $stream = $storage->readStream($key);
        $this->assertSame('hello world', stream_get_contents($stream));
        fclose($stream);

        // Delete
        $storage->delete($key);
        $this->assertFalse($storage->exists($key));

        // Idempotent delete
        $storage->delete($key); // Should not throw
    }
}
