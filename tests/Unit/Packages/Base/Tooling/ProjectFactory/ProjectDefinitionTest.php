<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Tooling\ProjectFactory;

use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDefinition;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectIdentity;
use PHPUnit\Framework\TestCase;

final class ProjectDefinitionTest extends TestCase
{
    public function test_definition(): void
    {
        $def = new ProjectDefinition(
            new ProjectIdentity('T', 't', 'T'),
            [new ManifestDependency('package', 'P1', '^1', true)],
            [new ManifestDependency('capability', 'C1', '^1', true)],
        );

        self::assertCount(2, $def->allSelections());
    }
}
