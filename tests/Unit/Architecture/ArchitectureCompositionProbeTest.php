<?php

declare(strict_types=1);

namespace Tests\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class ArchitectureCompositionProbeTest extends TestCase
{
    #[DataProvider('invalidCompositionProbes')]
    public function test_invalid_composition_is_rejected(
        string $probe,
        string $expectedError,
    ): void {
        $fixtureDirectory = $this->createFixtureDirectory();

        try {
            $this->buildProbe($fixtureDirectory, $probe);

            self::assertContains(
                $expectedError,
                $this->validateComposition($fixtureDirectory),
            );
        } finally {
            $this->removeDirectory($fixtureDirectory);
        }
    }

    public function test_valid_base_extension_and_product_composition_passes(): void
    {
        $fixtureDirectory = $this->createFixtureDirectory();

        try {
            $this->buildValidComposition($fixtureDirectory);

            self::assertSame([], $this->validateComposition($fixtureDirectory));
        } finally {
            $this->removeDirectory($fixtureDirectory);
        }
    }

    /** @return iterable<string, array{string, string}> */
    public static function invalidCompositionProbes(): iterable
    {
        yield 'Extension attempting Base internal access fails' => [
            'extension-internal-access',
            'extension.customer-profile imports Base internal class Probe\Base\Identity\Infrastructure\InternalIdentityService',
        ];

        yield 'Module requiring undeclared capability fails' => [
            'undeclared-capability',
            'module.orders consumes undeclared capability notification.send',
        ];

        yield 'Missing required capability provider fails' => [
            'missing-provider',
            'module.orders requires capability search.query but no provider is available',
        ];

        yield 'Duplicate capability providers without resolution fails' => [
            'duplicate-providers',
            'capability notification.send has multiple providers and no resolution strategy',
        ];
    }

    private function buildProbe(string $fixtureDirectory, string $probe): void
    {
        match ($probe) {
            'extension-internal-access' => $this->buildExtensionInternalAccessProbe($fixtureDirectory),
            'undeclared-capability' => $this->buildUndeclaredCapabilityProbe($fixtureDirectory),
            'missing-provider' => $this->buildMissingProviderProbe($fixtureDirectory),
            'duplicate-providers' => $this->buildDuplicateProvidersProbe($fixtureDirectory),
            default => throw new RuntimeException('Unknown architecture composition probe: '.$probe),
        };
    }

    private function buildExtensionInternalAccessProbe(string $fixtureDirectory): void
    {
        $this->writeComponent(
            $fixtureDirectory,
            'packages/base/Foundation/Identity',
            $this->manifest(
                'base.identity',
                'Foundation',
                'Probe\Base\Identity',
            ),
            [
                'Infrastructure/InternalIdentityService.php' => $this->implementation(
                    'Probe\Base\Identity\Infrastructure',
                    'InternalIdentityService',
                ),
            ],
        );
        $this->writeComponent(
            $fixtureDirectory,
            'extensions/Base/CustomerProfile',
            $this->manifest(
                'extension.customer-profile',
                'Extension',
                'Probe\Extension\CustomerProfile',
            ),
            [
                'CustomerProfileExtension.php' => $this->consumer(
                    'Probe\Extension\CustomerProfile',
                    'CustomerProfileExtension',
                    'Probe\Base\Identity\Infrastructure\InternalIdentityService',
                ),
            ],
        );
        $this->writeComposition($fixtureDirectory);
    }

    private function buildUndeclaredCapabilityProbe(string $fixtureDirectory): void
    {
        $this->writeComponent(
            $fixtureDirectory,
            'packages/base/Platform/Notifications',
            $this->manifest(
                'base.notifications',
                'Platform',
                'Probe\Base\Notifications',
                provides: [[
                    'capability' => 'notification.send',
                    'contract' => 'Probe\Base\Notifications\PublicApi\NotificationSender',
                    'provider' => 'base.notifications.default',
                ]],
            ),
            [
                'PublicApi/NotificationSender.php' => $this->contract(
                    'Probe\Base\Notifications\PublicApi',
                    'NotificationSender',
                ),
            ],
        );
        $this->writeComponent(
            $fixtureDirectory,
            'modules/Orders',
            $this->manifest(
                'module.orders',
                'Product',
                'Probe\Modules\Orders',
            ),
            [
                'Application/OrderNotifier.php' => $this->consumer(
                    'Probe\Modules\Orders\Application',
                    'OrderNotifier',
                    'Probe\Base\Notifications\PublicApi\NotificationSender',
                ),
            ],
        );
        $this->writeComposition($fixtureDirectory);
    }

    private function buildMissingProviderProbe(string $fixtureDirectory): void
    {
        $this->writeComponent(
            $fixtureDirectory,
            'modules/Orders',
            $this->manifest(
                'module.orders',
                'Product',
                'Probe\Modules\Orders',
                requires: ['search.query'],
            ),
        );
        $this->writeComposition($fixtureDirectory);
    }

    private function buildDuplicateProvidersProbe(string $fixtureDirectory): void
    {
        $this->writeComponent(
            $fixtureDirectory,
            'packages/base/Platform/EmailNotifications',
            $this->manifest(
                'base.notifications.email',
                'Platform',
                'Probe\Base\EmailNotifications',
                provides: [[
                    'capability' => 'notification.send',
                    'contract' => 'Probe\Base\EmailNotifications\PublicApi\EmailSender',
                    'provider' => 'base.notifications.email',
                ]],
            ),
        );
        $this->writeComponent(
            $fixtureDirectory,
            'extensions/Base/SmsNotifications',
            $this->manifest(
                'extension.notifications.sms',
                'Extension',
                'Probe\Extension\SmsNotifications',
                provides: [[
                    'capability' => 'notification.send',
                    'contract' => 'Probe\Extension\SmsNotifications\PublicApi\SmsSender',
                    'provider' => 'extension.notifications.sms',
                ]],
            ),
        );
        $this->writeComposition($fixtureDirectory);
    }

    private function buildValidComposition(string $fixtureDirectory): void
    {
        $this->writeComponent(
            $fixtureDirectory,
            'packages/base/Foundation/Identity',
            $this->manifest(
                'base.identity',
                'Foundation',
                'Probe\Base\Identity',
                provides: [[
                    'capability' => 'identity.profile.extend',
                    'contract' => 'Probe\Base\Identity\PublicApi\ProfileExtension',
                    'provider' => 'base.identity.profile-extension',
                ]],
            ),
            [
                'PublicApi/ProfileExtension.php' => $this->contract(
                    'Probe\Base\Identity\PublicApi',
                    'ProfileExtension',
                ),
            ],
        );
        $this->writeComponent(
            $fixtureDirectory,
            'packages/base/Platform/Notifications',
            $this->manifest(
                'base.notifications',
                'Platform',
                'Probe\Base\Notifications',
                provides: [[
                    'capability' => 'notification.send',
                    'contract' => 'Probe\Base\Notifications\PublicApi\NotificationSender',
                    'provider' => 'base.notifications.default',
                ]],
            ),
            [
                'PublicApi/NotificationSender.php' => $this->contract(
                    'Probe\Base\Notifications\PublicApi',
                    'NotificationSender',
                ),
            ],
        );
        $this->writeComponent(
            $fixtureDirectory,
            'extensions/Base/CustomerProfile',
            $this->manifest(
                'extension.customer-profile',
                'Extension',
                'Probe\Extension\CustomerProfile',
                requires: ['identity.profile.extend'],
            ),
            [
                'CustomerProfileExtension.php' => $this->implementation(
                    'Probe\Extension\CustomerProfile',
                    'CustomerProfileExtension',
                    'Probe\Base\Identity\PublicApi\ProfileExtension',
                ),
            ],
        );
        $this->writeComponent(
            $fixtureDirectory,
            'modules/Orders',
            $this->manifest(
                'module.orders',
                'Product',
                'Probe\Modules\Orders',
                requires: ['notification.send'],
            ),
            [
                'Application/OrderNotifier.php' => $this->consumer(
                    'Probe\Modules\Orders\Application',
                    'OrderNotifier',
                    'Probe\Base\Notifications\PublicApi\NotificationSender',
                ),
            ],
        );
        $this->writeComposition($fixtureDirectory);
    }

    /** @return list<string> */
    private function validateComposition(string $fixtureDirectory): array
    {
        $components = $this->loadComponents($fixtureDirectory);
        $providers = [];
        $contractCapabilities = [];
        $errors = [];

        foreach ($components as $component) {
            foreach ($component['provides'] as $provided) {
                $providers[$provided['capability']][] = $provided['provider'];
                $contractCapabilities[$provided['contract']] = $provided['capability'];
            }
        }

        foreach ($components as $component) {
            foreach ($component['requires'] as $capability) {
                if (($providers[$capability] ?? []) === []) {
                    $errors[] = sprintf(
                        '%s requires capability %s but no provider is available',
                        $component['id'],
                        $capability,
                    );
                }
            }

            foreach ($this->phpDependencies($component['path']) as $dependency) {
                $target = $this->componentOwningNamespace($components, $dependency);

                if (
                    $component['category'] === 'Extension'
                    && $target !== null
                    && in_array($target['category'], ['Foundation', 'Platform', 'Specialized'], true)
                    && ! str_contains($dependency, '\\PublicApi\\')
                ) {
                    $errors[] = sprintf(
                        '%s imports Base internal class %s',
                        $component['id'],
                        $dependency,
                    );
                }

                $capability = $contractCapabilities[$dependency] ?? null;

                if (
                    $capability !== null
                    && $target !== null
                    && $target['id'] !== $component['id']
                    && ! in_array($capability, $component['requires'], true)
                ) {
                    $errors[] = sprintf(
                        '%s consumes undeclared capability %s',
                        $component['id'],
                        $capability,
                    );
                }
            }
        }

        $resolutions = $this->loadResolutions($fixtureDirectory);

        foreach ($providers as $capability => $capabilityProviders) {
            if (count($capabilityProviders) < 2) {
                continue;
            }

            $selectedProvider = $resolutions[$capability] ?? null;

            if ($selectedProvider === null) {
                $errors[] = sprintf(
                    'capability %s has multiple providers and no resolution strategy',
                    $capability,
                );
            } elseif (! in_array($selectedProvider, $capabilityProviders, true)) {
                $errors[] = sprintf(
                    'capability %s resolves to unavailable provider %s',
                    $capability,
                    $selectedProvider,
                );
            }
        }

        return array_values(array_unique($errors));
    }

    /**
     * @return list<array{
     *     id: string,
     *     category: string,
     *     namespace: string,
     *     requires: list<string>,
     *     provides: list<array{capability: string, contract: string, provider: string}>,
     *     path: string
     * }>
     */
    private function loadComponents(string $fixtureDirectory): array
    {
        $components = [];
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fixtureDirectory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || $file->getFilename() !== 'module.json') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if ($contents === false) {
                throw new RuntimeException('Unable to read temporary component manifest.');
            }

            /** @var array{
             *     id: string,
             *     category: string,
             *     namespace: string,
             *     requires?: list<string>,
             *     provides?: list<array{capability: string, contract: string, provider: string}>
             * } $manifest
             */
            $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
            $components[] = [
                'id' => $manifest['id'],
                'category' => $manifest['category'],
                'namespace' => $manifest['namespace'],
                'requires' => $manifest['requires'] ?? [],
                'provides' => $manifest['provides'] ?? [],
                'path' => $file->getPath(),
            ];
        }

        return $components;
    }

    /**
     * @param  list<array{id: string, category: string, namespace: string}>  $components
     * @return array{id: string, category: string, namespace: string}|null
     */
    private function componentOwningNamespace(array $components, string $dependency): ?array
    {
        foreach ($components as $component) {
            if (str_starts_with($dependency, $component['namespace'].'\\')) {
                return $component;
            }
        }

        return null;
    }

    /** @return list<string> */
    private function phpDependencies(string $componentPath): array
    {
        $dependencies = [];
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($componentPath, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if ($contents === false) {
                throw new RuntimeException('Unable to read temporary PHP fixture.');
            }

            preg_match_all(
                '/\\buse\\s+([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)\\s*;/',
                $contents,
                $matches,
            );

            foreach ($matches[1] as $dependency) {
                $dependencies[] = $dependency;
            }
        }

        return array_values(array_unique($dependencies));
    }

    /** @return array<string, string> */
    private function loadResolutions(string $fixtureDirectory): array
    {
        $contents = file_get_contents($fixtureDirectory.'/composition.json');

        if ($contents === false) {
            throw new RuntimeException('Unable to read temporary composition metadata.');
        }

        /** @var array{resolutions?: array<string, string>} $composition */
        $composition = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        return $composition['resolutions'] ?? [];
    }

    /**
     * @param  list<string>  $requires
     * @param  list<array{capability: string, contract: string, provider: string}>  $provides
     * @return array{
     *     id: string,
     *     category: string,
     *     namespace: string,
     *     requires: list<string>,
     *     provides: list<array{capability: string, contract: string, provider: string}>
     * }
     */
    private function manifest(
        string $id,
        string $category,
        string $namespace,
        array $requires = [],
        array $provides = [],
    ): array {
        return compact('id', 'category', 'namespace', 'requires', 'provides');
    }

    /**
     * @param array{
     *     id: string,
     *     category: string,
     *     namespace: string,
     *     requires: list<string>,
     *     provides: list<array{capability: string, contract: string, provider: string}>
     * } $manifest
     * @param  array<string, string>  $files
     */
    private function writeComponent(
        string $fixtureDirectory,
        string $relativePath,
        array $manifest,
        array $files = [],
    ): void {
        $componentPath = $fixtureDirectory.'/'.$relativePath;
        $this->writeFile(
            $componentPath.'/module.json',
            (string) json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        );

        foreach ($files as $file => $contents) {
            $this->writeFile($componentPath.'/'.$file, $contents);
        }
    }

    /** @param array<string, string> $resolutions */
    private function writeComposition(string $fixtureDirectory, array $resolutions = []): void
    {
        $this->writeFile(
            $fixtureDirectory.'/composition.json',
            (string) json_encode(['resolutions' => $resolutions], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        );
    }

    private function writeFile(string $path, string $contents): void
    {
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create temporary probe path: '.$directory);
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Unable to write temporary probe file: '.$path);
        }
    }

    private function createFixtureDirectory(): string
    {
        $directory = sys_get_temp_dir().'/base-b1-composition-'.bin2hex(random_bytes(8));

        if (! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create temporary composition fixture directory.');
        }

        return $directory;
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($directory);
    }

    private function contract(string $namespace, string $name): string
    {
        return "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$namespace};\n\ninterface {$name}\n{\n}\n";
    }

    private function consumer(string $namespace, string $name, string $dependency): string
    {
        $dependencyName = substr($dependency, (int) strrpos($dependency, '\\') + 1);

        return "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$namespace};\n\nuse {$dependency};\n\nfinal class {$name}\n{\n    public function __construct(private {$dependencyName} \$dependency)\n    {\n    }\n}\n";
    }

    private function implementation(
        string $namespace,
        string $name,
        ?string $contract = null,
    ): string {
        $use = $contract === null ? '' : "\nuse {$contract};\n";
        $contractName = $contract === null
            ? null
            : substr($contract, (int) strrpos($contract, '\\') + 1);
        $implements = $contractName === null ? '' : " implements {$contractName}";

        return "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$namespace};\n{$use}\nfinal class {$name}{$implements}\n{\n}\n";
    }
}
