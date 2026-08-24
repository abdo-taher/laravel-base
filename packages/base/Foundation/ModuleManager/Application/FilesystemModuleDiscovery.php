<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Application;

use Base\Foundation\Manifest\Public\Contracts\ManifestReader;
use Base\Foundation\Manifest\Public\Exceptions\InvalidManifest;
use Base\Foundation\Manifest\Public\Exceptions\ManifestReadFailure;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use Base\Foundation\ModuleManager\Public\Contracts\ModuleDiscovery;
use Base\Foundation\ModuleManager\Public\Exceptions\ModuleDiscoveryFailed;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Discovers module manifests by recursively scanning filesystem paths for
 * `module.json` files and delegating parsing to ManifestReader.
 *
 * Fail-closed: an unreadable search path or an invalid manifest raises
 * ModuleDiscoveryFailed immediately.
 */
final class FilesystemModuleDiscovery implements ModuleDiscovery
{
    private const string MANIFEST_FILENAME = 'module.json';

    public function __construct(private readonly ManifestReader $reader) {}

    /** @return list<Manifest> */
    public function discover(iterable $searchPaths): array
    {
        $manifests = [];

        foreach ($searchPaths as $searchPath) {
            if (! is_dir($searchPath) || ! is_readable($searchPath)) {
                throw ModuleDiscoveryFailed::unreadablePath($searchPath);
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $searchPath,
                    RecursiveDirectoryIterator::SKIP_DOTS,
                ),
                RecursiveIteratorIterator::SELF_FIRST,
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getFilename() !== self::MANIFEST_FILENAME) {
                    continue;
                }

                $path = $file->getRealPath();

                if ($path === false || ! is_readable($path)) {
                    throw ModuleDiscoveryFailed::unreadablePath((string) $file->getPathname());
                }

                try {
                    $manifests[] = $this->reader->read($path);
                } catch (InvalidManifest $e) {
                    throw ModuleDiscoveryFailed::invalidManifest($path, $e->getMessage());
                } catch (ManifestReadFailure $e) {
                    throw ModuleDiscoveryFailed::unreadablePath($path);
                }
            }
        }

        return $manifests;
    }
}
