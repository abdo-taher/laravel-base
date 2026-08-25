<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ProductBoundaryArchitectureTest extends TestCase
{
    /**
     * Proves that a Product module cannot import internal classes of another Product module.
     * E.g., Modules\Cart\... cannot import Modules\Wallet\Domain\...
     */
    public function test_product_modules_cannot_import_foreign_internals(): void
    {
        $modulesDir = __DIR__.'/../../../modules';

        if (! is_dir($modulesDir)) {
            $this->markTestSkipped('No modules directory to scan yet.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesDir, \FilesystemIterator::SKIP_DOTS)
        );

        $violations = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if ($content === false) {
                continue;
            }

            // Extract the current module name from the file path
            // e.g. modules/Cart/Application/Command.php -> Cart
            $relativePath = str_replace(realpath($modulesDir).'/', '', $file->getRealPath());
            $parts = explode('/', $relativePath);
            $currentModule = $parts[0];

            preg_match_all('/use\s+Modules\\\\([A-Za-z0-9_]+)\\\\(.+?);/', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $targetModule = $match[1];
                $targetNamespace = $match[2]; // e.g. "Domain\Something" or "Public\Contracts\Interface"

                if ($currentModule === $targetModule) {
                    continue; // Intra-module imports are fine
                }

                if (! str_starts_with($targetNamespace, 'Public\\')) {
                    $violations[] = sprintf(
                        'File %s imports internal foreign class Modules\\%s\\%s',
                        $relativePath,
                        $targetModule,
                        $targetNamespace
                    );
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Found Product -> foreign Product internal boundary violations:\n".implode("\n", $violations)
        );
    }
}
