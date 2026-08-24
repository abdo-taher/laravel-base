<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry\Public\ValueObjects;

use Base\Foundation\ExtensionRegistry\Public\Contracts\ExtensionContract;
use InvalidArgumentException;

final readonly class ContributionDefinition
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $id,
        public string $point,
        public ExtensionContract $extension,
        public int $priority = 0,
        public array $metadata = [],
    ) {
        if (trim($id) === '' || trim($point) === '') {
            throw new InvalidArgumentException('Contribution ID and extension point must be non-empty strings.');
        }
    }
}
