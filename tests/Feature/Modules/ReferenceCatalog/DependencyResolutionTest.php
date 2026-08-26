<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\ReferenceCatalog;

use Base\Foundation\DependencyResolver\Application\ManifestDependencyResolver;
use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\ModuleManager\Application\FilesystemModuleDiscovery;
use PHPUnit\Framework\TestCase;

final class DependencyResolutionTest extends TestCase
{
    public function test_resolves_reference_catalog_and_media_and_files_transitively(): void
    {
        $factory = new ManifestFactory;
        $reader = new JsonManifestReader($factory);
        $discovery = new FilesystemModuleDiscovery($reader);

        $basePaths = [
            __DIR__.'/../../../../packages/base/Foundation',
            __DIR__.'/../../../../packages/base/Platform',
            __DIR__.'/../../../../packages/base/Specialized',
            __DIR__.'/../../../../modules',
        ];

        $manifests = [];
        foreach ($basePaths as $path) {
            $manifests = array_merge($manifests, $discovery->discover([$path]));
        }

        $resolver = new ManifestDependencyResolver;
        $result = $resolver->resolve($manifests);

        $orderedNames = array_map(fn ($node) => $node->name(), $result->orderedNodes);

        $this->assertContains('Modules.ReferenceCatalog', $orderedNames);
        $this->assertContains('Base.Platform.Media', $orderedNames);
        $this->assertContains('Base.Platform.Files', $orderedNames);

        // Assert ordering is correct: Files before Media, Media before ReferenceCatalog
        $filesIndex = array_search('Base.Platform.Files', $orderedNames, true);
        $mediaIndex = array_search('Base.Platform.Media', $orderedNames, true);
        $catalogIndex = array_search('Modules.ReferenceCatalog', $orderedNames, true);

        $this->assertLessThan($mediaIndex, $filesIndex);
        $this->assertLessThan($catalogIndex, $mediaIndex);
    }
}
