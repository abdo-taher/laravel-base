<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest\Application;

use Base\Foundation\Manifest\Public\Exceptions\InvalidManifest;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestCapability;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;
use Base\Foundation\SharedKernel\Public\ValueObjects\SemanticVersion;

final class ManifestFactory
{
    private const CATEGORIES = [
        'Foundation',
        'Platform',
        'Specialized',
        'Product',
        'Extension',
    ];

    private const OWNERSHIP_VALUES = [
        'base-owned',
        'project-owned',
        'generated-managed',
        'protected',
    ];

    public function create(mixed $data): Manifest
    {
        if (! is_array($data)) {
            throw new InvalidManifest(['manifest root must be a JSON object']);
        }

        $errors = [];
        $name = $this->requiredString($data, 'name', $errors);
        $category = $this->requiredString($data, 'category', $errors);
        $version = $this->requiredString($data, 'version', $errors);
        $namespace = $this->requiredString($data, 'namespace', $errors);
        $ownership = $this->requiredString($data, 'ownership', $errors);

        if ($category !== '' && ! in_array($category, self::CATEGORIES, true)) {
            $errors[] = 'category must be one of: '.implode(', ', self::CATEGORIES);
        }

        if ($version !== '' && ! SemanticVersion::isValid($version)) {
            $errors[] = 'version must use semantic versioning';
        }

        if ($namespace !== '' && ! $this->isNamespace($namespace)) {
            $errors[] = 'namespace must be a valid PHP namespace';
        }

        if ($ownership !== '' && ! in_array($ownership, self::OWNERSHIP_VALUES, true)) {
            $errors[] = 'ownership must be one of: '.implode(', ', self::OWNERSHIP_VALUES);
        }

        $dependencies = $this->dependencies($data['dependencies'] ?? [], $errors);
        $capabilities = $this->capabilities($data['provides'] ?? [], $errors);

        if ($errors !== []) {
            throw new InvalidManifest($errors);
        }

        return new Manifest(
            name: $name,
            category: $category,
            version: $version,
            namespace: $namespace,
            ownership: $ownership,
            dependencies: $dependencies,
            capabilities: $capabilities,
        );
    }

    /**
     * @param  array<mixed>  $data
     * @param  list<string>  $errors
     */
    private function requiredString(array $data, string $field, array &$errors): string
    {
        $value = $data[$field] ?? null;

        if (! is_string($value) || trim($value) === '') {
            $errors[] = sprintf('%s is required and must be a non-empty string', $field);

            return '';
        }

        return $value;
    }

    /**
     * @param  list<string>  $errors
     * @return list<ManifestDependency>
     */
    private function dependencies(mixed $data, array &$errors): array
    {
        if (! is_array($data) || (array_is_list($data) && $data !== [])) {
            $errors[] = 'dependencies must be an object containing required and optional lists';

            return [];
        }

        $dependencies = [];

        foreach (['required' => true, 'optional' => false] as $group => $required) {
            $entries = $data[$group] ?? [];

            if (! is_array($entries) || ! array_is_list($entries)) {
                $errors[] = sprintf('dependencies.%s must be a list', $group);

                continue;
            }

            foreach ($entries as $index => $entry) {
                if (! is_array($entry) || array_is_list($entry)) {
                    $errors[] = sprintf('dependencies.%s.%d must be an object', $group, $index);

                    continue;
                }

                $capability = $this->nonEmptyString($entry['capability'] ?? null);
                $package = $this->nonEmptyString($entry['package'] ?? null);
                $constraint = $this->nonEmptyString($entry['version'] ?? null);

                if (($capability === null) === ($package === null)) {
                    $errors[] = sprintf(
                        'dependencies.%s.%d must declare exactly one capability or package target',
                        $group,
                        $index,
                    );
                }

                if ($constraint === null) {
                    $errors[] = sprintf('dependencies.%s.%d.version must be a non-empty string', $group, $index);
                }

                $target = $capability ?? $package;
                if ((($capability === null) === ($package === null)) || $constraint === null || $target === null) {
                    continue;
                }

                $dependencies[] = new ManifestDependency(
                    targetType: $capability !== null ? 'capability' : 'package',
                    target: $target,
                    version: $constraint,
                    required: $required,
                );
            }
        }

        return $dependencies;
    }

    /**
     * @param  list<string>  $errors
     * @return list<ManifestCapability>
     */
    private function capabilities(mixed $data, array &$errors): array
    {
        if (! is_array($data) || ! array_is_list($data)) {
            $errors[] = 'provides must be a list of capability declarations';

            return [];
        }

        $capabilities = [];

        foreach ($data as $index => $entry) {
            if (! is_array($entry) || array_is_list($entry)) {
                $errors[] = sprintf('provides.%d must be an object', $index);

                continue;
            }

            $capability = $this->nonEmptyString($entry['capability'] ?? null);
            $version = $this->nonEmptyString($entry['version'] ?? null);

            if ($capability === null) {
                $errors[] = sprintf('provides.%d.capability must be a non-empty string', $index);
            }

            if ($version === null || ! SemanticVersion::isValid($version)) {
                $errors[] = sprintf('provides.%d.version must use semantic versioning', $index);
            }

            if ($capability === null || $version === null || ! SemanticVersion::isValid($version)) {
                continue;
            }

            $capabilities[] = new ManifestCapability($capability, $version);
        }

        return $capabilities;
    }

    private function nonEmptyString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function isNamespace(string $namespace): bool
    {
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*(?:\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $namespace) === 1;
    }
}
