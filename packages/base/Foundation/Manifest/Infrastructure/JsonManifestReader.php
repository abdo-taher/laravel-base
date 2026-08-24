<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest\Infrastructure;

use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Public\Contracts\ManifestReader;
use Base\Foundation\Manifest\Public\Exceptions\ManifestReadFailure;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use JsonException;

final readonly class JsonManifestReader implements ManifestReader
{
    public function __construct(private ManifestFactory $factory) {}

    public function read(string $path): Manifest
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw ManifestReadFailure::unreadable($path);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw ManifestReadFailure::unreadable($path);
        }

        try {
            $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw ManifestReadFailure::invalidJson($path, $exception);
        }

        return $this->factory->create($data);
    }
}
