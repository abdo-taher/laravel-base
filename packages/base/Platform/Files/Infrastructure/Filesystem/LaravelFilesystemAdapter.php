<?php

declare(strict_types=1);

namespace Base\Platform\Files\Infrastructure\Filesystem;

use Base\Platform\Files\Public\Contracts\FileStorage;
use Base\Platform\Files\Public\Exceptions\FileNotFound;
use Base\Platform\Files\Public\Exceptions\FileStorageFailed;
use Base\Platform\Files\Public\ValueObjects\FileVisibility;
use Base\Platform\Files\Public\ValueObjects\StorageKey;
use Illuminate\Contracts\Filesystem\Filesystem;
use Throwable;

final readonly class LaravelFilesystemAdapter implements FileStorage
{
    public function __construct(
        private Filesystem $filesystem
    ) {}

    public function read(StorageKey|string $key): string
    {
        $keyString = (string) $key;

        try {
            if (! $this->filesystem->exists($keyString)) {
                throw FileNotFound::forKey($keyString);
            }

            $content = $this->filesystem->get($keyString);
            if ($content === null) {
                throw new \RuntimeException('Filesystem returned null instead of string');
            }

            return $content;
        } catch (FileNotFound $e) {
            throw $e;
        } catch (Throwable $e) {
            throw FileStorageFailed::onRead($keyString, $e);
        }
    }

    public function readStream(StorageKey|string $key)
    {
        $keyString = (string) $key;

        try {
            if (! $this->filesystem->exists($keyString)) {
                throw FileNotFound::forKey($keyString);
            }

            $stream = $this->filesystem->readStream($keyString);

            if (! is_resource($stream)) {
                throw new \RuntimeException('Filesystem failed to return a valid resource stream');
            }

            return $stream;
        } catch (FileNotFound $e) {
            throw $e;
        } catch (Throwable $e) {
            throw FileStorageFailed::onRead($keyString, $e);
        }
    }

    public function exists(StorageKey|string $key): bool
    {
        $keyString = (string) $key;

        try {
            return $this->filesystem->exists($keyString);
        } catch (Throwable $e) {
            throw FileStorageFailed::onCheck($keyString, $e);
        }
    }

    public function write(StorageKey|string $key, $contents, FileVisibility $visibility = FileVisibility::PRIVATE): void
    {
        $keyString = (string) $key;
        $options = ['visibility' => $visibility->value];

        try {
            $success = $this->filesystem->put($keyString, $contents, $options);

            if (! $success) {
                throw new \RuntimeException('Filesystem operation returned false.');
            }
        } catch (Throwable $e) {
            throw FileStorageFailed::onWrite($keyString, $e);
        }
    }

    public function delete(StorageKey|string $key): void
    {
        $keyString = (string) $key;

        try {
            if ($this->filesystem->exists($keyString)) {
                $success = $this->filesystem->delete($keyString);
                if (! $success) {
                    throw new \RuntimeException('Filesystem delete operation returned false.');
                }
            }
        } catch (Throwable $e) {
            throw FileStorageFailed::onDelete($keyString, $e);
        }
    }
}
