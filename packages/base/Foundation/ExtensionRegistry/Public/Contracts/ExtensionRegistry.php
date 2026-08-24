<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry\Public\Contracts;

use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ExtensionDefinition;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ExtensionPoint;

interface ExtensionRegistry
{
    public function registerPoint(ExtensionPoint $point): void;

    public function register(ExtensionDefinition $extension): void;

    public function extensionPoint(string $name): ExtensionPoint;

    /** @return list<ContributorContract> */
    public function contributors(string $point): array;

    /** @return list<DecoratorContract> */
    public function decorators(string $point): array;

    /** @return list<StrategyContract> */
    public function strategies(string $point): array;

    /** @return list<MetadataExtensionContract> */
    public function metadataExtensions(string $point): array;
}
