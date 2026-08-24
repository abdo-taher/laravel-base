<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Public\Contracts;

use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use Base\Foundation\ModuleManager\Public\Exceptions\ModuleDiscoveryFailed;

/**
 * Discovers package and module manifests from the filesystem.
 *
 * Implementations scan the given search paths for `module.json` files
 * and return the fully parsed Manifest objects.
 *
 * Fail-closed: unreadable paths and invalid manifests must throw
 * ModuleDiscoveryFailed rather than silently returning partial results.
 *
 * No Laravel types. No hard-coded module list.
 */
interface ModuleDiscovery
{
    /**
     * @param  iterable<string>  $searchPaths  Filesystem paths to scan recursively.
     * @return list<Manifest>
     *
     * @throws ModuleDiscoveryFailed
     */
    public function discover(iterable $searchPaths): array;
}
