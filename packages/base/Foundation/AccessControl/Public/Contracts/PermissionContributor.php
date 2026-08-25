<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl\Public\Contracts;

use Base\Foundation\AccessControl\Public\ValueObjects\Permission;

/**
 * Extension hook: declares permissions owned by a module.
 *
 * Business modules implement this contract to register their
 * permission names with the AccessControl subsystem. AccessControl
 * aggregates declared permissions for discovery and validation but
 * does not own the permission semantics.
 *
 * Example:
 *
 *   final class ExamplePermissionContributor implements PermissionContributor
 *   {
 *       public function permissions(): array
 *       {
 *           return [
 *               new Permission('example.view'),
 *               new Permission('example.transfer'),
 *           ];
 *       }
 *   }
 *
 * Compatible with the existing contributor model
 * (ConfigurationSourceContributor pattern). Wiring to the full
 * ExtensionRegistry runtime is deferred to post-B3.
 *
 * No framework dependencies.
 */
interface PermissionContributor
{
    /**
     * @return list<Permission>
     */
    public function permissions(): array;
}
