<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry\Public\Attributes;

use Attribute;
use Base\Foundation\ExtensionRegistry\Public\Contracts\ExtensionMetadataContract;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class ExtensionMetadata implements ExtensionMetadataContract
{
    public function __construct(
        private string $extensionId,
        private string $extensionPoint,
        private string $contributionId,
        private int $priority = 0,
    ) {
        if (trim($extensionId) === '' || trim($extensionPoint) === '' || trim($contributionId) === '') {
            throw new InvalidArgumentException('Extension attribute identifiers must be non-empty strings.');
        }
    }

    public function extensionId(): string
    {
        return $this->extensionId;
    }

    public function extensionPoint(): string
    {
        return $this->extensionPoint;
    }

    public function contributionId(): string
    {
        return $this->contributionId;
    }

    public function priority(): int
    {
        return $this->priority;
    }
}
