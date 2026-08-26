<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Presentation\Cli;

use RuntimeException;

final class ProjectDefinitionParserException extends RuntimeException
{
    public static function invalidJson(string $error): self
    {
        return new self('Invalid JSON definition: '.$error);
    }

    public static function missingField(string $field): self
    {
        return new self('Missing required field in JSON definition: '.$field);
    }

    public static function invalidType(string $field, string $expected): self
    {
        return new self(sprintf('Invalid type for field %s. Expected %s.', $field, $expected));
    }
}
