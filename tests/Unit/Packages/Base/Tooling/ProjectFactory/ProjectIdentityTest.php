<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Tooling\ProjectFactory;

use Base\Tooling\ProjectFactory\Public\Exceptions\InvalidProjectIdentity;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectIdentity;
use PHPUnit\Framework\TestCase;

final class ProjectIdentityTest extends TestCase
{
    public function test_valid_identity(): void
    {
        $id = new ProjectIdentity('My Project', 'my-project', 'App');
        self::assertSame('My Project', $id->name);
        self::assertSame('my-project', $id->slug);
        self::assertSame('App', $id->namespace);
    }

    public function test_invalid_name(): void
    {
        $this->expectException(InvalidProjectIdentity::class);
        $this->expectExceptionMessage('Project name must be a non-empty');
        new ProjectIdentity('   ', 'my-project', 'App');
    }

    public function test_invalid_slug(): void
    {
        $this->expectException(InvalidProjectIdentity::class);
        new ProjectIdentity('My Project', 'my_project!', 'App');
    }

    public function test_invalid_namespace(): void
    {
        $this->expectException(InvalidProjectIdentity::class);
        new ProjectIdentity('My Project', 'my-project', 'app\\Invalid');
    }
}
