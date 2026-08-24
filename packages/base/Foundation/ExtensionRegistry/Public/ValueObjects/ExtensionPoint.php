<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry\Public\ValueObjects;

use Base\Foundation\ExtensionRegistry\Public\Contracts\ContributorContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\DecoratorContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\ExtensionContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\MetadataExtensionContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\StrategyContract;
use InvalidArgumentException;

final readonly class ExtensionPoint
{
    public const CONTRIBUTOR = 'contributor';

    public const DECORATOR = 'decorator';

    public const STRATEGY = 'strategy';

    public const METADATA = 'metadata';

    /** @param class-string<ExtensionContract> $contract */
    public function __construct(
        public string $name,
        public string $kind,
        public string $contract,
        public bool $multiple = true,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Extension point name must be a non-empty string.');
        }

        $marker = match ($kind) {
            self::CONTRIBUTOR => ContributorContract::class,
            self::DECORATOR => DecoratorContract::class,
            self::STRATEGY => StrategyContract::class,
            self::METADATA => MetadataExtensionContract::class,
            default => throw new InvalidArgumentException(sprintf('Unsupported extension point kind: %s', $kind)),
        };

        if (! is_a($contract, $marker, true)) {
            throw new InvalidArgumentException(sprintf(
                'Extension point contract %s must extend %s',
                $contract,
                $marker,
            ));
        }
    }
}
