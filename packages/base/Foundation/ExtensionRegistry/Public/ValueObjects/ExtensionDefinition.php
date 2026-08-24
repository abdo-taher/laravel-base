<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry\Public\ValueObjects;

use InvalidArgumentException;

final readonly class ExtensionDefinition
{
    /**
     * @param  list<ContributionDefinition>  $contributions
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $id,
        public bool $enabled,
        public array $contributions,
        public array $metadata = [],
    ) {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Extension ID must be a non-empty string.');
        }
    }
}
