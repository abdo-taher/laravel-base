<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\ReferenceCatalog;

use PHPUnit\Framework\TestCase;

final class ProductArchitectureTest extends TestCase
{
    public function test_reference_catalog_public_contains_no_internal_dependencies(): void
    {
        $directory = __DIR__.'/../../../../modules/ReferenceCatalog/Public';

        if (! is_dir($directory)) {
            $this->markTestSkipped('No Public directory to scan.');
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        $violations = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            preg_match_all('/use\s+(Illuminate|Symfony|App\\\\Models|Base\\\\Platform\\\\Media\\\\(?!Public)|Modules\\\\ReferenceCatalog\\\\(?!Public))\\\\(.+?);/', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $violations[] = sprintf('File %s imports forbidden class %s\%s', $file->getFilename(), $match[1], $match[2]);
            }
        }
        $this->assertEmpty($violations, "Found forbidden dependencies in Product Public API:\n".implode("\n", $violations));
    }
}
