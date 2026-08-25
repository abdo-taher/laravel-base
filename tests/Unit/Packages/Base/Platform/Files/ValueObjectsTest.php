<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Files;

use Base\Platform\Files\Public\Exceptions\InvalidStorageKey;
use Base\Platform\Files\Public\ValueObjects\StorageKey;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_storage_key_accepts_valid_string(): void
    {
        $key = new StorageKey('documents/report.pdf');
        $this->assertSame('documents/report.pdf', $key->value);
        $this->assertSame('documents/report.pdf', (string) $key);
    }

    public function test_storage_key_rejects_empty_string(): void
    {
        $this->expectException(InvalidStorageKey::class);
        new StorageKey('   ');
    }

    public function test_storage_key_rejects_null_bytes(): void
    {
        $this->expectException(InvalidStorageKey::class);
        new StorageKey("file\0.txt");
    }

    public function test_storage_key_rejects_traversal(): void
    {
        $this->expectException(InvalidStorageKey::class);
        new StorageKey('../file.txt');
    }

    public function test_storage_key_rejects_backward_traversal(): void
    {
        $this->expectException(InvalidStorageKey::class);
        new StorageKey('..\\file.txt');
    }

    public function test_storage_key_rejects_absolute_path(): void
    {
        $this->expectException(InvalidStorageKey::class);
        new StorageKey('/etc/passwd');
    }

    public function test_storage_key_rejects_absolute_windows_path(): void
    {
        $this->expectException(InvalidStorageKey::class);
        new StorageKey('\\Windows\\System32');
    }

    public function test_storage_key_rejects_windows_drive_letter_path(): void
    {
        $this->expectException(InvalidStorageKey::class);
        new StorageKey('C:/absolute/path');
    }
}
