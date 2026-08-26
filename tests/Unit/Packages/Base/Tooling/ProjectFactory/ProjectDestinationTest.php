<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Tooling\ProjectFactory;

use Base\Tooling\ProjectFactory\Public\Exceptions\InvalidPlannedPath;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDestination;
use PHPUnit\Framework\TestCase;

final class ProjectDestinationTest extends TestCase
{
    public function test_valid_absolute_path(): void
    {
        $dest = new ProjectDestination('/var/www/my-project');
        self::assertSame('/var/www/my-project', $dest->value);
    }

    public function test_rejects_relative_path(): void
    {
        $this->expectException(InvalidPlannedPath::class);
        new ProjectDestination('my-project');
    }

    public function test_rejects_traversal(): void
    {
        $this->expectException(InvalidPlannedPath::class);
        new ProjectDestination('/var/www/../my-project');
    }
}
