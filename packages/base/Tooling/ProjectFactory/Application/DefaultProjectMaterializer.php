<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Application;

use Base\Tooling\ProjectFactory\Infrastructure\MaterializationSourceResolver;
use Base\Tooling\ProjectFactory\Public\Contracts\ProjectMaterializer;
use Base\Tooling\ProjectFactory\Public\Exceptions\ProjectMaterializationFailed;
use Base\Tooling\ProjectFactory\Public\ValueObjects\FactoryExecutionResult;
use Base\Tooling\ProjectFactory\Public\ValueObjects\GenerationPlan;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\CopyTemplateOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\CopyTreeOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\CreateDirectoryOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\GenerateProvidersBootstrapOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDestination;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class DefaultProjectMaterializer implements ProjectMaterializer
{
    public function __construct(
        private readonly MaterializationSourceResolver $sourceResolver,
    ) {}

    public function materialize(GenerationPlan $plan, ProjectDestination $destination): FactoryExecutionResult
    {
        if (file_exists($destination->value)) {
            throw ProjectMaterializationFailed::destinationExists($destination->value);
        }

        $staging = dirname($destination->value).'/.base-factory-'.bin2hex(random_bytes(8));

        if (! mkdir($staging, 0755, true) && ! is_dir($staging)) {
            throw new ProjectMaterializationFailed('Failed to create staging workspace.');
        }

        $published = false;
        $operationsExecuted = 0;

        try {
            $this->copyHostSkeleton($staging);
            $this->renderComposerJson($staging, $plan);

            foreach ($plan->filesystemOperations as $operation) {
                if ($operation instanceof CreateDirectoryOperation) {
                    $this->createDirectory($staging, $operation);
                } elseif ($operation instanceof CopyTreeOperation) {
                    $this->copyTree($staging, $operation);
                } elseif ($operation instanceof CopyTemplateOperation) {
                    $this->copyTemplate($staging, $operation);
                } elseif ($operation instanceof GenerateProvidersBootstrapOperation) {
                    $this->generateProvidersBootstrap($staging, $operation);
                } else {
                    throw new ProjectMaterializationFailed('Unknown operation type: '.$operation::class);
                }
                $operationsExecuted++;
            }

            if (file_exists($destination->value)) {
                throw ProjectMaterializationFailed::destinationRace($destination->value);
            }

            if (! rename($staging, $destination->value)) {
                throw ProjectMaterializationFailed::atomicPublishFailed($staging, $destination->value);
            }

            $published = true;
        } finally {
            if (! $published && is_dir($staging)) {
                $this->cleanup($staging);
            }
        }

        return new FactoryExecutionResult(
            identity: $plan->identity,
            destination: $destination,
            operationsExecuted: $operationsExecuted,
            published: $published,
        );
    }

    private function copyHostSkeleton(string $staging): void
    {
        $skeletonDir = $this->sourceResolver->resolveTemplateDirectory('project-host');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($skeletonDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            $subPath = $iterator->getSubPathName();
            if ($subPath === 'composer.json.template') {
                continue; // Handled separately
            }

            $target = $staging.'/'.$subPath;

            if ($item->isLink()) {
                throw ProjectMaterializationFailed::symlinkRejected($item->getPathname());
            }

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755);
                }
            } else {
                copy($item->getPathname(), $target);
                if (in_array(basename($subPath), ['artisan'], true)) {
                    chmod($target, 0755);
                }
            }
        }
    }

    private function renderComposerJson(string $staging, GenerationPlan $plan): void
    {
        $templatePath = $this->sourceResolver->resolveTemplateDirectory('project-host').'/composer.json.template';
        $content = (string) file_get_contents($templatePath);

        $content = str_replace('{{ PROJECT_SLUG }}', $plan->identity->slug, $content);

        file_put_contents($staging.'/composer.json', $content);
    }

    private function generateProvidersBootstrap(string $staging, GenerateProvidersBootstrapOperation $operation): void
    {
        $code = "<?php\n\nreturn [\n    ".implode(",\n    ", $operation->providers).",\n];\n";
        file_put_contents($staging.'/'.$operation->targetPath->value, $code);
    }

    private function createDirectory(string $staging, CreateDirectoryOperation $operation): void
    {
        $target = $staging.'/'.$operation->targetPath->value;
        if (file_exists($target)) {
            if (! is_dir($target)) {
                throw ProjectMaterializationFailed::outputConflict($operation->targetPath->value);
            }

            return;
        }
        mkdir($target, 0755, true);
    }

    private function copyTree(string $staging, CopyTreeOperation $operation): void
    {
        $sourceDir = $this->sourceResolver->resolvePackage($operation->sourcePackageName);
        $targetDir = $staging.'/'.$operation->targetPath->value;

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            $subPath = $iterator->getSubPathName();
            $target = $targetDir.'/'.$subPath;

            if ($item->isLink()) {
                throw ProjectMaterializationFailed::symlinkRejected($item->getPathname());
            }

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private function copyTemplate(string $staging, CopyTemplateOperation $operation): void
    {
        $sourceFile = $this->sourceResolver->resolveTemplate($operation->template);
        $targetFile = $staging.'/'.$operation->targetPath->value;

        $dir = dirname($targetFile);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = (string) file_get_contents($sourceFile);
        foreach ($operation->substitutions as $key => $value) {
            $content = str_replace('{{ '.$key.' }}', $value, $content);
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }

        file_put_contents($targetFile, $content);
    }

    private function cleanup(string $dir): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
