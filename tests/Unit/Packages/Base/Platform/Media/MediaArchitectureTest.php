<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Media;

use PHPUnit\Framework\TestCase;

final class MediaArchitectureTest extends TestCase
{
    public function test_public_contracts_have_no_framework_dependencies(): void
    {
        $directory = __DIR__.'/../../../../../../packages/base/Platform/Media/Public';

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

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

            preg_match_all('/use\s+(Illuminate|Symfony)\\\\(.+?);/', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $violations[] = sprintf(
                    'File %s imports framework class %s\%s',
                    $file->getFilename(),
                    $match[1],
                    $match[2]
                );
            }
        }

        $this->assertEmpty($violations, "Found framework dependencies in Public API:\n".implode("\n", $violations));
    }
}
