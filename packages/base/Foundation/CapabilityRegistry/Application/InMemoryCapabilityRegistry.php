<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Application;

use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityResolver;
use Base\Foundation\CapabilityRegistry\Public\Exceptions\CapabilityResolutionFailed;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityName;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityProviderDefinition;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityResolutionResult;

final class InMemoryCapabilityRegistry implements CapabilityResolver
{
    /** @var array<string, list<CapabilityProviderDefinition>> */
    private array $providers = [];

    public function __construct(private readonly VersionConstraintMatcher $matcher) {}

    public function register(CapabilityProviderDefinition $definition): void
    {
        $this->providers[$definition->name->value][] = $definition;
    }

    public function resolve(
        CapabilityName $name,
        string $versionConstraint,
        bool $required = true,
        ?string $strategy = null,
    ): CapabilityResolutionResult {
        $registered = $this->providers[$name->value] ?? [];

        if ($registered === []) {
            if ($required) {
                throw CapabilityResolutionFailed::missing($name->value);
            }

            return new CapabilityResolutionResult($name, $versionConstraint, false, null);
        }

        if ($strategy !== null) {
            $registered = array_values(array_filter(
                $registered,
                static fn (CapabilityProviderDefinition $definition): bool => $definition->strategy === $strategy,
            ));

            if ($registered === []) {
                throw CapabilityResolutionFailed::strategyUnavailable($name->value, $strategy);
            }
        }

        $compatible = array_values(array_filter(
            $registered,
            fn (CapabilityProviderDefinition $definition): bool => $this->matcher->matches(
                $definition->version,
                $versionConstraint,
            ),
        ));

        if ($compatible === []) {
            throw CapabilityResolutionFailed::incompatible($name->value, $versionConstraint);
        }

        if (count($compatible) > 1) {
            throw CapabilityResolutionFailed::ambiguous($name->value, $versionConstraint);
        }

        return new CapabilityResolutionResult($name, $versionConstraint, $required, $compatible[0]);
    }
}
