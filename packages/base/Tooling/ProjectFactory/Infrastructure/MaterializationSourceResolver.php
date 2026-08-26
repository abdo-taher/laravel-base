<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Infrastructure;

use Base\Tooling\ProjectFactory\Public\Exceptions\ProjectMaterializationFailed;
use Base\Tooling\ProjectFactory\Public\ValueObjects\TemplateReference;

final readonly class MaterializationSourceResolver
{
    public function __construct(private string $repositoryRoot) {}

    public function resolvePackage(string $packageName): string
    {
        $segments = explode('.', $packageName);

        if (count($segments) === 3 && $segments[0] === 'Base') {
            $layer = $segments[1];
            $name = $segments[2];
            $path = $this->repositoryRoot.'/packages/base/'.$layer.'/'.$name;
        } elseif (count($segments) === 2 && $segments[0] === 'Modules') {
            $name = $segments[1];
            $path = $this->repositoryRoot.'/modules/'.$name;
        } else {
            throw ProjectMaterializationFailed::unsafeSource('Unknown package structure: '.$packageName);
        }

        if (! is_dir($path)) {
            throw ProjectMaterializationFailed::unsafeSource('Resolved package path does not exist: '.$path);
        }

        return $path;
    }

    public function resolveTemplate(TemplateReference $reference): string
    {
        $path = $this->repositoryRoot.'/templates/'.$reference->value;

        if (! is_file($path)) {
            throw ProjectMaterializationFailed::unsafeSource('Resolved template file does not exist: '.$path);
        }

        return $path;
    }

    public function resolveTemplateDirectory(string $name): string
    {
        $path = $this->repositoryRoot.'/templates/'.$name;

        if (! is_dir($path)) {
            throw ProjectMaterializationFailed::unsafeSource('Resolved template directory does not exist: '.$path);
        }

        return $path;
    }
}
