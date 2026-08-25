<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Files;

use Base\Platform\Files\FilesServiceProvider;
use PHPUnit\Framework\TestCase;

final class NamespaceTest extends TestCase
{
    public function test_files_service_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(FilesServiceProvider::class));
    }
}
