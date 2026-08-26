<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Verification;

use PHPUnit\Framework\TestCase;

final class VerificationArchitectureTest extends TestCase
{
    public function test_public_contracts_have_no_framework_dependencies(): void
    {
        $directory = __DIR__.'/../../../../../../packages/base/Platform/Verification/Public';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        $violations = [];

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            preg_match_all('/use\s+(Illuminate|Symfony|App\\\\Models|Base\\\\Platform\\\\Notifications|Base\\\\Foundation\\\\Identity|Modules)\\\\(.+?);/', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $violations[] = sprintf('File %s imports forbidden class %s\%s', $file->getFilename(), $match[1], $match[2]);
            }
        }
        $this->assertEmpty($violations, "Found forbidden dependencies in Public API:\n".implode("\n", $violations));
    }
}
