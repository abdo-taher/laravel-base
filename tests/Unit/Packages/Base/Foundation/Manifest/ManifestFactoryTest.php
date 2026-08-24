<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Manifest;

use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Public\Exceptions\InvalidManifest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ManifestFactoryTest extends TestCase
{
    public function test_it_hydrates_a_valid_manifest(): void
    {
        $manifest = (new ManifestFactory)->create(self::validManifestData());

        self::assertSame('Manifest', $manifest->name);
        self::assertSame('Foundation', $manifest->category);
        self::assertSame('1.2.3', $manifest->version);
        self::assertSame('Base\Foundation\Manifest', $manifest->namespace);
        self::assertSame('base-owned', $manifest->ownership);
        self::assertCount(2, $manifest->dependencies);
        self::assertTrue($manifest->dependencies[0]->required);
        self::assertFalse($manifest->dependencies[1]->required);
        self::assertSame('manifest.read', $manifest->capabilities[0]->name);
    }

    #[DataProvider('missingRequiredFields')]
    public function test_it_rejects_missing_required_fields(string $field): void
    {
        $data = self::validManifestData();
        unset($data[$field]);

        $this->expectException(InvalidManifest::class);
        $this->expectExceptionMessage($field.' is required');

        (new ManifestFactory)->create($data);
    }

    /** @return iterable<string, array{string}> */
    public static function missingRequiredFields(): iterable
    {
        foreach (['name', 'category', 'version', 'namespace', 'ownership'] as $field) {
            yield $field => [$field];
        }
    }

    /** @param callable(array<string, mixed>): mixed $mutate */
    #[DataProvider('invalidManifestStructures')]
    public function test_it_rejects_invalid_manifest_structures(
        callable $mutate,
        string $expectedError,
    ): void {
        $this->expectException(InvalidManifest::class);
        $this->expectExceptionMessage($expectedError);

        (new ManifestFactory)->create($mutate(self::validManifestData()));
    }

    /** @return iterable<string, array{callable(array<string, mixed>): mixed, string}> */
    public static function invalidManifestStructures(): iterable
    {
        yield 'root is not an object' => [
            static fn (array $data): string => 'invalid',
            'manifest root must be a JSON object',
        ];
        yield 'category is unsupported' => [
            static fn (array $data): array => [...$data, 'category' => 'Unknown'],
            'category must be one of',
        ];
        yield 'version is not semantic' => [
            static fn (array $data): array => [...$data, 'version' => '1.0'],
            'version must use semantic versioning',
        ];
        yield 'namespace is invalid' => [
            static fn (array $data): array => [...$data, 'namespace' => 'Base/Manifest'],
            'namespace must be a valid PHP namespace',
        ];
        yield 'ownership is unsupported' => [
            static fn (array $data): array => [...$data, 'ownership' => 'foreign'],
            'ownership must be one of',
        ];
        yield 'dependencies is a list' => [
            static fn (array $data): array => [...$data, 'dependencies' => ['invalid']],
            'dependencies must be an object',
        ];
        yield 'required dependencies is not a list' => [
            static fn (array $data): array => [
                ...$data,
                'dependencies' => ['required' => ['key' => 'value']],
            ],
            'dependencies.required must be a list',
        ];
        yield 'dependency entry is not an object' => [
            static fn (array $data): array => [
                ...$data,
                'dependencies' => ['required' => ['invalid']],
            ],
            'dependencies.required.0 must be an object',
        ];
        yield 'dependency has both targets' => [
            static fn (array $data): array => [
                ...$data,
                'dependencies' => ['required' => [[
                    'capability' => 'identity.read',
                    'package' => 'Identity',
                    'version' => '^1.0',
                ]]],
            ],
            'must declare exactly one capability or package target',
        ];
        yield 'dependency has no target' => [
            static fn (array $data): array => [
                ...$data,
                'dependencies' => ['required' => [['version' => '^1.0']]],
            ],
            'must declare exactly one capability or package target',
        ];
        yield 'dependency has no version' => [
            static fn (array $data): array => [
                ...$data,
                'dependencies' => ['required' => [['capability' => 'identity.read']]],
            ],
            'dependencies.required.0.version must be a non-empty string',
        ];
        yield 'provides is not a list' => [
            static fn (array $data): array => [...$data, 'provides' => ['capability' => 'manifest.read']],
            'provides must be a list',
        ];
        yield 'provided capability is not an object' => [
            static fn (array $data): array => [...$data, 'provides' => ['manifest.read']],
            'provides.0 must be an object',
        ];
        yield 'provided capability has no name' => [
            static fn (array $data): array => [...$data, 'provides' => [['version' => '1.0.0']]],
            'provides.0.capability must be a non-empty string',
        ];
        yield 'provided capability has invalid version' => [
            static fn (array $data): array => [
                ...$data,
                'provides' => [['capability' => 'manifest.read', 'version' => '^1.0']],
            ],
            'provides.0.version must use semantic versioning',
        ];
    }

    /** @return array<string, mixed> */
    public static function validManifestData(): array
    {
        return [
            'name' => 'Manifest',
            'category' => 'Foundation',
            'version' => '1.2.3',
            'namespace' => 'Base\Foundation\Manifest',
            'ownership' => 'base-owned',
            'dependencies' => [
                'required' => [[
                    'capability' => 'filesystem.read',
                    'version' => '^1.0',
                ]],
                'optional' => [[
                    'package' => 'Observability',
                    'version' => '^2.0',
                ]],
            ],
            'provides' => [[
                'capability' => 'manifest.read',
                'version' => '1.0.0',
            ]],
        ];
    }
}
