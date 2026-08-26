<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Presentation\Cli;

use Base\Foundation\DependencyResolver\Application\ManifestDependencyResolver;
use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\ModuleManager\Application\FilesystemModuleDiscovery;
use Base\Tooling\ProjectFactory\Application\DefaultProjectMaterializer;
use Base\Tooling\ProjectFactory\Application\DefaultProjectPlanner;
use Base\Tooling\ProjectFactory\Infrastructure\MaterializationSourceResolver;
use Base\Tooling\ProjectFactory\Public\Exceptions\InvalidPlannedPath;
use Base\Tooling\ProjectFactory\Public\Exceptions\InvalidProjectIdentity;
use Base\Tooling\ProjectFactory\Public\Exceptions\ProjectMaterializationFailed;
use Base\Tooling\ProjectFactory\Public\Exceptions\ProjectPlannerException;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDestination;
use RuntimeException;

final class FactoryApplication
{
    private const CODE_SUCCESS = 0;

    private const CODE_GENERAL_ERROR = 1;

    private const CODE_INPUT_ERROR = 2;

    private const CODE_PLAN_ERROR = 3;

    private const CODE_MATERIALIZE_ERROR = 4;

    public function __construct(private readonly string $repositoryRoot) {}

    /**
     * @param  array<int, string>  $argv
     */
    public function run(array $argv): int
    {
        $command = $argv[1] ?? null;

        if ($command === null || $command === '--help' || $command === '-h') {
            $this->printHelp();

            return self::CODE_SUCCESS;
        }

        try {
            if ($command === 'plan') {
                return $this->runPlan($argv);
            }

            if ($command === 'generate') {
                return $this->runGenerate($argv);
            }

            $this->err("Unknown command: $command");
            $this->printHelp();

            return self::CODE_INPUT_ERROR;
        } catch (ProjectDefinitionParserException|InvalidProjectIdentity|InvalidPlannedPath $e) {
            $this->err('Input Error: '.$e->getMessage());

            return self::CODE_INPUT_ERROR;
        } catch (ProjectPlannerException $e) {
            $this->err('Planning Error: '.$e->getMessage());

            return self::CODE_PLAN_ERROR;
        } catch (ProjectMaterializationFailed $e) {
            $this->err('Materialization Error: '.$e->getMessage());

            return self::CODE_MATERIALIZE_ERROR;
        } catch (RuntimeException $e) {
            $this->err('System Error: '.$e->getMessage());

            return self::CODE_GENERAL_ERROR;
        }
    }

    /**
     * @param  array<int, string>  $argv
     */
    private function runPlan(array $argv): int
    {
        $file = null;
        $json = false;

        for ($i = 2; $i < count($argv); $i++) {
            if ($argv[$i] === '--json') {
                $json = true;
            } elseif (str_starts_with($argv[$i], '-')) {
                throw new ProjectDefinitionParserException('Unknown option: '.$argv[$i]);
            } else {
                if ($file === null) {
                    $file = $argv[$i];
                } else {
                    throw new ProjectDefinitionParserException('Too many arguments. Only one JSON file allowed.');
                }
            }
        }

        if ($file === null) {
            throw new ProjectDefinitionParserException('Missing JSON file argument.');
        }

        $parser = new JsonProjectDefinitionParser;
        $def = $parser->parseFile($file);

        $planner = $this->buildPlanner();
        $plan = $planner->plan($def);

        $renderer = new PlanRenderer;
        if ($json) {
            $this->out($renderer->renderJson($plan));
        } else {
            $this->out($renderer->renderText($plan));
        }

        return self::CODE_SUCCESS;
    }

    /**
     * @param  array<int, string>  $argv
     */
    private function runGenerate(array $argv): int
    {
        $file = null;
        $destPath = null;

        for ($i = 2; $i < count($argv); $i++) {
            if (str_starts_with($argv[$i], '--destination=')) {
                $destPath = substr($argv[$i], 14);
            } elseif (str_starts_with($argv[$i], '-')) {
                throw new ProjectDefinitionParserException('Unknown option: '.$argv[$i]);
            } else {
                if ($file === null) {
                    $file = $argv[$i];
                } else {
                    throw new ProjectDefinitionParserException('Too many arguments. Only one JSON file allowed.');
                }
            }
        }

        if ($file === null) {
            throw new ProjectDefinitionParserException('Missing JSON file argument.');
        }
        if ($destPath === null) {
            throw new ProjectDefinitionParserException('Missing --destination argument.');
        }

        $dest = new ProjectDestination($destPath);

        $parser = new JsonProjectDefinitionParser;
        $def = $parser->parseFile($file);

        $planner = $this->buildPlanner();
        $plan = $planner->plan($def);

        $materializer = new DefaultProjectMaterializer(
            new MaterializationSourceResolver($this->repositoryRoot)
        );

        $result = $materializer->materialize($plan, $dest);

        $this->out(sprintf('Generated project "%s"', $result->identity->slug));
        $this->out(sprintf('Destination: %s', $result->destination->value));
        $this->out(sprintf('Operations: %d', $result->operationsExecuted));

        return self::CODE_SUCCESS;
    }

    private function buildPlanner(): DefaultProjectPlanner
    {
        $reader = new JsonManifestReader(new ManifestFactory);
        $discovery = new FilesystemModuleDiscovery($reader);

        $paths = [
            $this->repositoryRoot.'/packages/base/Foundation',
            $this->repositoryRoot.'/packages/base/Platform',
            $this->repositoryRoot.'/packages/base/Specialized',
            $this->repositoryRoot.'/modules',
        ];

        $manifests = $discovery->discover($paths);

        return new DefaultProjectPlanner(
            new ManifestDependencyResolver,
            $manifests
        );
    }

    private function printHelp(): void
    {
        $this->out('Project Factory CLI');
        $this->out('');
        $this->out('Usage:');
        $this->out('  bin/factory plan <file.json> [--json]');
        $this->out('  bin/factory generate <file.json> --destination=<path>');
    }

    private function out(string $message): void
    {
        echo $message."\n";
    }

    private function err(string $message): void
    {
        file_put_contents('php://stderr', $message."\n");
    }
}
