<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry\Application;

use Base\Foundation\ExtensionRegistry\Public\Contracts\ContributorContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\DecoratorContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\ExtensionContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\ExtensionRegistry;
use Base\Foundation\ExtensionRegistry\Public\Contracts\MetadataExtensionContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\StrategyContract;
use Base\Foundation\ExtensionRegistry\Public\Exceptions\ExtensionRegistrationFailed;
use Base\Foundation\ExtensionRegistry\Public\Exceptions\ExtensionResolutionFailed;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ContributionDefinition;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ExtensionDefinition;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ExtensionPoint;

final class InMemoryExtensionRegistry implements ExtensionRegistry
{
    /** @var array<string, ExtensionPoint> */
    private array $points = [];

    /** @var array<string, ExtensionDefinition> */
    private array $extensions = [];

    /** @var array<string, array<string, array{extension: string, contribution: ContributionDefinition}>> */
    private array $contributions = [];

    public function registerPoint(ExtensionPoint $point): void
    {
        if (isset($this->points[$point->name])) {
            throw new ExtensionRegistrationFailed(sprintf(
                'Extension point is already registered: %s',
                $point->name,
            ));
        }

        $this->points[$point->name] = $point;
    }

    public function register(ExtensionDefinition $extension): void
    {
        if (! $extension->enabled) {
            throw new ExtensionRegistrationFailed(sprintf(
                'Disabled extension cannot be registered: %s',
                $extension->id,
            ));
        }

        if (isset($this->extensions[$extension->id])) {
            throw new ExtensionRegistrationFailed(sprintf(
                'Extension is already registered: %s',
                $extension->id,
            ));
        }

        $pendingKeys = [];
        $pendingPointCounts = [];

        foreach ($extension->contributions as $contribution) {
            $point = $this->points[$contribution->point] ?? null;

            if ($point === null) {
                throw new ExtensionRegistrationFailed(sprintf(
                    'Unknown extension point: %s',
                    $contribution->point,
                ));
            }

            if (! is_a($contribution->extension, $point->contract)) {
                throw new ExtensionRegistrationFailed(sprintf(
                    'Contribution %s does not implement extension point contract %s',
                    $contribution->id,
                    $point->contract,
                ));
            }

            $this->validateKind($point, $contribution);
            $key = $contribution->point.':'.$contribution->id;

            if (isset($pendingKeys[$key]) || isset($this->contributions[$contribution->point][$contribution->id])) {
                throw new ExtensionRegistrationFailed(sprintf(
                    'Duplicate incompatible contribution: %s',
                    $key,
                ));
            }

            $pendingPointCounts[$contribution->point] = ($pendingPointCounts[$contribution->point] ?? 0) + 1;

            if (! $point->multiple && (
                ($this->contributions[$contribution->point] ?? []) !== []
                || $pendingPointCounts[$contribution->point] > 1
            )) {
                throw new ExtensionRegistrationFailed(sprintf(
                    'Extension point %s accepts only one contribution',
                    $point->name,
                ));
            }

            $pendingKeys[$key] = true;
        }

        $this->extensions[$extension->id] = $extension;

        foreach ($extension->contributions as $contribution) {
            $this->contributions[$contribution->point][$contribution->id] = [
                'extension' => $extension->id,
                'contribution' => $contribution,
            ];
        }
    }

    public function extensionPoint(string $name): ExtensionPoint
    {
        return $this->points[$name] ?? throw new ExtensionResolutionFailed(sprintf(
            'Extension point is not registered: %s',
            $name,
        ));
    }

    public function contributors(string $point): array
    {
        return $this->collect($point, ExtensionPoint::CONTRIBUTOR, ContributorContract::class);
    }

    public function decorators(string $point): array
    {
        return $this->collect($point, ExtensionPoint::DECORATOR, DecoratorContract::class);
    }

    public function strategies(string $point): array
    {
        return $this->collect($point, ExtensionPoint::STRATEGY, StrategyContract::class);
    }

    public function metadataExtensions(string $point): array
    {
        return $this->collect($point, ExtensionPoint::METADATA, MetadataExtensionContract::class);
    }

    private function validateKind(ExtensionPoint $point, ContributionDefinition $contribution): void
    {
        $valid = match ($point->kind) {
            ExtensionPoint::CONTRIBUTOR => $contribution->extension instanceof ContributorContract,
            ExtensionPoint::DECORATOR => $contribution->extension instanceof DecoratorContract,
            ExtensionPoint::STRATEGY => $contribution->extension instanceof StrategyContract,
            ExtensionPoint::METADATA => $contribution->extension instanceof MetadataExtensionContract,
            default => false,
        };

        if (! $valid) {
            throw new ExtensionRegistrationFailed(sprintf(
                'Contribution %s is incompatible with extension point kind %s',
                $contribution->id,
                $point->kind,
            ));
        }
    }

    /**
     * @template T of ExtensionContract
     *
     * @param  class-string<T>  $contract
     * @return list<T>
     */
    private function collect(string $pointName, string $kind, string $contract): array
    {
        $point = $this->extensionPoint($pointName);

        if ($point->kind !== $kind) {
            throw new ExtensionResolutionFailed(sprintf(
                'Extension point %s is %s, not %s',
                $pointName,
                $point->kind,
                $kind,
            ));
        }

        $entries = array_values($this->contributions[$pointName] ?? []);
        usort($entries, static fn (array $left, array $right): int => [
            -$left['contribution']->priority,
            $left['extension'],
            $left['contribution']->id,
        ] <=> [
            -$right['contribution']->priority,
            $right['extension'],
            $right['contribution']->id,
        ]);
        $resolved = [];

        foreach ($entries as $entry) {
            $extension = $entry['contribution']->extension;

            if (! is_a($extension, $contract)) {
                throw new ExtensionResolutionFailed(sprintf(
                    'Registered contribution %s no longer satisfies %s',
                    $entry['contribution']->id,
                    $contract,
                ));
            }

            $resolved[] = $extension;
        }

        return $resolved;
    }
}
