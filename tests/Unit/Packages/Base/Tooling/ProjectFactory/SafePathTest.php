<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Tooling\ProjectFactory;

use Base\Tooling\ProjectFactory\Public\Exceptions\InvalidPlannedPath;
use Base\Tooling\ProjectFactory\Public\ValueObjects\SafePath;
use PHPUnit\Framework\TestCase;

final class SafePathTest extends TestCase
{
    public function test_valid_path(): void
    {
        $path = new SafePath('src/Domain/Models');
        self::assertSame('src/Domain/Models', $path->value);
    }

    public function test_rejects_absolute(): void
    {
        $this->expectException(InvalidPlannedPath::class);
        new SafePath('/etc/passwd');
    }

    public function test_rejects_traversal(): void
    {
        $this->expectException(InvalidPlannedPath::class);
        new SafePath('src/../config');
    }
}
