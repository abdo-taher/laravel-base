<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects;

use Base\Tooling\ProjectFactory\Public\Exceptions\InvalidProjectIdentity;

final readonly class ProjectIdentity
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $namespace,
    ) {
        $this->validateName($name);
        $this->validateSlug($slug);
        $this->validateNamespace($namespace);
    }

    private function validateName(string $name): void
    {
        if (trim($name) === '') {
            throw InvalidProjectIdentity::invalidName();
        }
    }

    private function validateSlug(string $slug): void
    {
        if (! preg_match('/^[a-z0-9-]+$/', $slug)) {
            throw InvalidProjectIdentity::invalidSlug($slug);
        }
    }

    private function validateNamespace(string $namespace): void
    {
        // Valid PHP namespace root (e.g. 'App', 'MyProject\Domain')
        if (! preg_match('/^[A-Z][a-zA-Z0-9_]*(\\\\[A-Z][a-zA-Z0-9_]*)*$/', $namespace)) {
            throw InvalidProjectIdentity::invalidNamespace($namespace);
        }
    }
}
