<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Files;

use Base\Platform\Files\Public\Contracts\FileReader;
use Base\Platform\Files\Public\Contracts\FileStorage;
use Base\Platform\Files\Public\Contracts\FileWriter;
use Base\Platform\Files\Public\Exceptions\FileException;
use Base\Platform\Files\Public\Exceptions\FileNotFound;
use Base\Platform\Files\Public\Exceptions\FileStorageFailed;
use Base\Platform\Files\Public\Exceptions\InvalidStorageKey;
use Base\Platform\Files\Public\ValueObjects\FileVisibility;
use Base\Platform\Files\Public\ValueObjects\StorageKey;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class FilesArchitectureTest extends TestCase
{
    public function test_public_contracts_have_no_framework_dependencies(): void
    {
        $contracts = [
            FileReader::class,
            FileStorage::class,
            FileWriter::class,
            FileException::class,
            FileNotFound::class,
            FileStorageFailed::class,
            InvalidStorageKey::class,
            FileVisibility::class,
            StorageKey::class,
        ];

        foreach ($contracts as $contract) {
            $reflection = new ReflectionClass($contract);
            $fileName = $reflection->getFileName();

            if ($fileName === false) {
                $this->fail("Could not find file for contract {$contract}");
            }

            $content = file_get_contents($fileName);
            if ($content === false) {
                $this->fail("Could not read file for contract {$contract}");
            }

            $this->assertStringNotContainsString(
                'Illuminate\\',
                $content,
                "Contract {$contract} must not depend on Laravel framework."
            );

            $this->assertStringNotContainsString(
                'League\\Flysystem',
                $content,
                "Contract {$contract} must not depend on Flysystem."
            );
        }
    }
}
