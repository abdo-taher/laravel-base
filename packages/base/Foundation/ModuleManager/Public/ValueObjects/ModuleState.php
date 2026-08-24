<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Public\ValueObjects;

/**
 * Foundation-only module state descriptor.
 *
 * Lifecycle states beyond B2.5 (enabled, disabled, etc.) are deferred.
 */
final readonly class ModuleState
{
    /** Module manifest was found and read from the filesystem. */
    public const string DISCOVERED = 'discovered';

    /**
     * Module passed manifest validation, dependency resolution,
     * capability registration, and extension registration.
     * It is included in the boot plan.
     */
    public const string READY = 'ready';

    public function __construct(
        public ModuleIdentifier $identifier,
        public string $state,
    ) {}

    public function isReady(): bool
    {
        return $this->state === self::READY;
    }
}
