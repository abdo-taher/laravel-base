<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Manifest;

use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\Manifest\Public\Exceptions\InvalidManifest;
use Base\Foundation\Manifest\Public\Exceptions\ManifestReadFailure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JsonManifestReaderTest extends TestCase
{
    public function test_it_reads_and_hydrates_a_json_manifest(): void
    {
        $path = $this->temporaryManifest(json_encode(
            $this->validManifestData(),
            JSON_THROW_ON_ERROR,
        ));

        try {
            $manifest = (new JsonManifestReader(new ManifestFactory))->read($path);

            self::assertSame('Manifest', $manifest->name);
            self::assertSame('manifest.read', $manifest->capabilities[0]->name);
        } finally {
            unlink($path);
        }
    }

    public function test_it_rejects_a_missing_file(): void
    {
        $this->expectException(ManifestReadFailure::class);
        $this->expectExceptionMessage('Manifest file is not readable');

        (new JsonManifestReader(new ManifestFactory))->read('/missing/module.json');
    }

    public function test_it_rejects_malformed_json(): void
    {
        $path = $this->temporaryManifest('{invalid');

        try {
            $this->expectException(ManifestReadFailure::class);
            $this->expectExceptionMessage('Manifest file contains invalid JSON');

            (new JsonManifestReader(new ManifestFactory))->read($path);
        } finally {
            unlink($path);
        }
    }

    public function test_it_validates_decoded_json(): void
    {
        $path = $this->temporaryManifest('{}');

        try {
            $this->expectException(InvalidManifest::class);
            $this->expectExceptionMessage('name is required');

            (new JsonManifestReader(new ManifestFactory))->read($path);
        } finally {
            unlink($path);
        }
    }

    /** @return array<string, mixed> */
    private function validManifestData(): array
    {
        return [
            'name' => 'Manifest',
            'category' => 'Foundation',
            'version' => '1.0.0',
            'namespace' => 'Base\Foundation\Manifest',
            'ownership' => 'base-owned',
            'dependencies' => [
                'required' => [],
                'optional' => [],
            ],
            'provides' => [[
                'capability' => 'manifest.read',
                'version' => '1.0.0',
            ]],
        ];
    }

    private function temporaryManifest(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'base-manifest-');

        if ($path === false || file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Unable to create temporary manifest fixture.');
        }

        return $path;
    }
}
