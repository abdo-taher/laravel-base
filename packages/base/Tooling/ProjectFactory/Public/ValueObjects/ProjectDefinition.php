<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects;

use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;

final readonly class ProjectDefinition
{
    /**
     * @param  list<ManifestDependency>  $explicitModules
     * @param  list<ManifestDependency>  $explicitCapabilities
     */
    public function __construct(
        public ProjectIdentity $identity,
        public array $explicitModules = [],
        public array $explicitCapabilities = [],
    ) {}

    /**
     * @return list<ManifestDependency>
     */
    public function allSelections(): array
    {
        return array_merge($this->explicitModules, $this->explicitCapabilities);
    }
}
