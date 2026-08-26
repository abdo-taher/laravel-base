<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects\Operations;

use Base\Tooling\ProjectFactory\Public\ValueObjects\SafePath;

final readonly class GenerateProvidersBootstrapOperation implements PlannedOperation
{
    /**
     * @param  list<string>  $providers
     */
    public function __construct(
        public SafePath $targetPath,
        public array $providers,
    ) {}

    public function description(): string
    {
        return 'Generate bootstrap/providers.php with '.count($this->providers).' providers';
    }
}
