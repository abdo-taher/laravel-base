<?php

declare(strict_types=1);

namespace Tests\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Process\Process;

final class ArchitectureValidationTest extends TestCase
{
    /**
     * @param  array<string, string>  $files
     * @param  array<string, string>  $layers
     * @param  array<string, list<string>>  $rules
     */
    #[DataProvider('architectureScenarios')]
    public function test_architecture_rule_is_enforced(
        array $files,
        array $layers,
        array $rules,
        bool $expectedToPass,
    ): void {
        $fixtureDirectory = $this->createFixtureDirectory();

        try {
            $this->writeFixtureFiles($fixtureDirectory, $files);
            $configPath = $this->writeDeptracConfig($fixtureDirectory, $layers, $rules);

            $process = new Process([
                $this->deptracExecutable(),
                'analyse',
                '--config-file='.$configPath,
                '--no-progress',
            ], dirname(__DIR__, 3));
            $process->setTimeout(30);
            $process->run();

            $output = $process->getOutput().$process->getErrorOutput();

            if ($expectedToPass) {
                self::assertSame(0, $process->getExitCode(), $output);
            } else {
                self::assertNotSame(0, $process->getExitCode(), $output);
            }
        } finally {
            $this->removeDirectory($fixtureDirectory);
        }
    }

    /**
     * @return iterable<string, array{
     *     array<string, string>,
     *     array<string, string>,
     *     array<string, list<string>>,
     *     bool
     * }>
     */
    public static function architectureScenarios(): iterable
    {
        yield 'Foundation importing Platform fails' => [
            [
                'packages/base/Foundation/Consumer/Application/Consumer.php' => self::consumer(
                    'Fixture\Foundation\Consumer\Application',
                    'Fixture\Platform\Provider\PublicApi\PlatformContract',
                ),
                'packages/base/Platform/Provider/PublicApi/PlatformContract.php' => self::contract(
                    'Fixture\Platform\Provider\PublicApi',
                    'PlatformContract',
                ),
            ],
            [
                'Foundation' => 'packages/base/Foundation/Consumer/.*',
                'PlatformPublic' => 'packages/base/Platform/Provider/PublicApi/.*',
            ],
            [
                'Foundation' => [],
                'PlatformPublic' => [],
            ],
            false,
        ];

        yield 'Platform importing Product fails' => [
            [
                'packages/base/Platform/Consumer/Application/Consumer.php' => self::consumer(
                    'Fixture\Platform\Consumer\Application',
                    'Fixture\Product\Provider\PublicApi\ProductContract',
                ),
                'modules/Provider/PublicApi/ProductContract.php' => self::contract(
                    'Fixture\Product\Provider\PublicApi',
                    'ProductContract',
                ),
            ],
            [
                'Platform' => 'packages/base/Platform/Consumer/.*',
                'ProductPublic' => 'modules/Provider/PublicApi/.*',
            ],
            [
                'Platform' => [],
                'ProductPublic' => [],
            ],
            false,
        ];

        yield 'Extension importing Base internal implementation fails' => [
            [
                'extensions/Base/Consumer/Consumer.php' => self::consumer(
                    'Fixture\Extension\Consumer',
                    'Fixture\Foundation\Provider\Infrastructure\InternalService',
                ),
                'packages/base/Foundation/Provider/Infrastructure/InternalService.php' => self::implementation(
                    'Fixture\Foundation\Provider\Infrastructure',
                    'InternalService',
                ),
            ],
            [
                'Extension' => 'extensions/Base/Consumer/.*',
                'BaseInternal' => 'packages/base/Foundation/Provider/Infrastructure/.*',
            ],
            [
                'Extension' => [],
                'BaseInternal' => [],
            ],
            false,
        ];

        yield 'Product importing another Product internal class fails' => [
            [
                'modules/Consumer/Application/Consumer.php' => self::consumer(
                    'Fixture\Product\Consumer\Application',
                    'Fixture\Product\Provider\Infrastructure\InternalService',
                ),
                'modules/Provider/Infrastructure/InternalService.php' => self::implementation(
                    'Fixture\Product\Provider\Infrastructure',
                    'InternalService',
                ),
            ],
            [
                'ProductConsumer' => 'modules/Consumer/.*',
                'ProductProviderInternal' => 'modules/Provider/Infrastructure/.*',
            ],
            [
                'ProductConsumer' => [],
                'ProductProviderInternal' => [],
            ],
            false,
        ];

        yield 'Circular dependency fails' => [
            [
                'modules/Alpha/Application/AlphaService.php' => self::consumer(
                    'Fixture\Product\Alpha\Application',
                    'Fixture\Product\Beta\PublicApi\BetaContract',
                    'AlphaService',
                ),
                'modules/Beta/PublicApi/BetaContract.php' => self::consumer(
                    'Fixture\Product\Beta\PublicApi',
                    'Fixture\Product\Alpha\Application\AlphaService',
                    'BetaContract',
                ),
            ],
            [
                'Alpha' => 'modules/Alpha/.*',
                'Beta' => 'modules/Beta/.*',
            ],
            [
                'Alpha' => ['Beta'],
                'Beta' => [],
            ],
            false,
        ];

        yield 'Platform importing Foundation Public contract passes' => [
            [
                'packages/base/Platform/Consumer/Application/Consumer.php' => self::consumer(
                    'Fixture\Platform\Consumer\Application',
                    'Fixture\Foundation\Provider\PublicApi\FoundationContract',
                ),
                'packages/base/Foundation/Provider/PublicApi/FoundationContract.php' => self::contract(
                    'Fixture\Foundation\Provider\PublicApi',
                    'FoundationContract',
                ),
            ],
            [
                'Platform' => 'packages/base/Platform/Consumer/.*',
                'FoundationPublic' => 'packages/base/Foundation/Provider/PublicApi/.*',
            ],
            [
                'Platform' => ['FoundationPublic'],
                'FoundationPublic' => [],
            ],
            true,
        ];

        yield 'Product importing Platform Public contract passes' => [
            [
                'modules/Consumer/Application/Consumer.php' => self::consumer(
                    'Fixture\Product\Consumer\Application',
                    'Fixture\Platform\Provider\PublicApi\PlatformContract',
                ),
                'packages/base/Platform/Provider/PublicApi/PlatformContract.php' => self::contract(
                    'Fixture\Platform\Provider\PublicApi',
                    'PlatformContract',
                ),
            ],
            [
                'Product' => 'modules/Consumer/.*',
                'PlatformPublic' => 'packages/base/Platform/Provider/PublicApi/.*',
            ],
            [
                'Product' => ['PlatformPublic'],
                'PlatformPublic' => [],
            ],
            true,
        ];

        yield 'Extension implementing Base extension contract passes' => [
            [
                'extensions/Base/Consumer/Extension.php' => self::implementation(
                    'Fixture\Extension\Consumer',
                    'Extension',
                    'Fixture\Foundation\Provider\PublicApi\ExtensionContract',
                ),
                'packages/base/Foundation/Provider/PublicApi/ExtensionContract.php' => self::contract(
                    'Fixture\Foundation\Provider\PublicApi',
                    'ExtensionContract',
                ),
            ],
            [
                'Extension' => 'extensions/Base/Consumer/.*',
                'BasePublic' => 'packages/base/Foundation/Provider/PublicApi/.*',
            ],
            [
                'Extension' => ['BasePublic'],
                'BasePublic' => [],
            ],
            true,
        ];
    }

    private function createFixtureDirectory(): string
    {
        $directory = sys_get_temp_dir().'/base-b1-architecture-'.bin2hex(random_bytes(8));

        if (! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create temporary architecture fixture directory.');
        }

        return $directory;
    }

    /** @param array<string, string> $files */
    private function writeFixtureFiles(string $fixtureDirectory, array $files): void
    {
        foreach ($files as $relativePath => $contents) {
            $path = $fixtureDirectory.'/'.$relativePath;
            $directory = dirname($path);

            if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
                throw new RuntimeException('Unable to create temporary fixture path: '.$directory);
            }

            if (file_put_contents($path, $contents) === false) {
                throw new RuntimeException('Unable to write temporary fixture: '.$path);
            }
        }
    }

    /**
     * @param  array<string, string>  $layerDirectories
     * @param  array<string, list<string>>  $rules
     */
    private function writeDeptracConfig(
        string $fixtureDirectory,
        array $layerDirectories,
        array $rules,
    ): string {
        $configPath = $fixtureDirectory.'/deptrac.php';
        $config = sprintf(
            <<<'PHP'
<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\DirectoryConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

return static function (DeptracConfig $config): void {
    $fixtureDirectory = %s;
    $layerDirectories = %s;
    $rules = %s;
    $layers = [];

    foreach ($layerDirectories as $name => $directory) {
        $layers[$name] = Layer::withName($name)->collectors(
            DirectoryConfig::create($directory),
        );
    }

    $rulesets = [];

    foreach ($rules as $source => $targets) {
        $ruleset = Ruleset::forLayer($layers[$source]);

        if ($targets !== []) {
            $ruleset->accesses(...array_map(
                static fn (string $target): Layer => $layers[$target],
                $targets,
            ));
        }

        $rulesets[] = $ruleset;
    }

    $config
        ->paths($fixtureDirectory)
        ->layers(...array_values($layers))
        ->rulesets(...$rulesets);
};
PHP,
            var_export($fixtureDirectory, true),
            var_export($layerDirectories, true),
            var_export($rules, true),
        );

        if (file_put_contents($configPath, $config) === false) {
            throw new RuntimeException('Unable to write temporary Deptrac configuration.');
        }

        return $configPath;
    }

    private function deptracExecutable(): string
    {
        $executable = realpath(dirname(__DIR__, 3).'/vendor/bin/deptrac');

        if ($executable === false) {
            throw new RuntimeException('Deptrac executable is unavailable.');
        }

        return $executable;
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

    private static function contract(string $namespace, string $name): string
    {
        return "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$namespace};\n\ninterface {$name}\n{\n}\n";
    }

    private static function consumer(
        string $namespace,
        string $dependency,
        string $name = 'Consumer',
    ): string {
        $dependencyName = substr($dependency, (int) strrpos($dependency, '\\') + 1);

        return "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$namespace};\n\nuse {$dependency};\n\nfinal class {$name}\n{\n    public function __construct(private {$dependencyName} \$dependency)\n    {\n    }\n}\n";
    }

    private static function implementation(
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
