<?php

declare(strict_types=1);

namespace Base\Platform\Files;

use Base\Platform\Files\Infrastructure\Filesystem\LaravelFilesystemAdapter;
use Base\Platform\Files\Public\Contracts\FileReader;
use Base\Platform\Files\Public\Contracts\FileStorage;
use Base\Platform\Files\Public\Contracts\FileWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

final class FilesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileStorage::class, function () {
            // By default, use the configured default disk.
            // This relies on the framework's filesystem manager.
            return new LaravelFilesystemAdapter(Storage::disk());
        });

        $this->app->bind(FileReader::class, FileStorage::class);
        $this->app->bind(FileWriter::class, FileStorage::class);
    }
}
