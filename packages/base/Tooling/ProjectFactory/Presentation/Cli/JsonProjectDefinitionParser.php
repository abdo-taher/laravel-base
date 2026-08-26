<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Presentation\Cli;

use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDefinition;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectIdentity;
use JsonException;

final class JsonProjectDefinitionParser
{
    public function parseFile(string $path): ProjectDefinition
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new ProjectDefinitionParserException('Definition file not found or unreadable: '.$path);
        }

        try {
            $content = file_get_contents($path);
            if ($content === false) {
                throw new ProjectDefinitionParserException('Failed to read definition file: '.$path);
            }
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw ProjectDefinitionParserException::invalidJson($e->getMessage());
        }

        if (! is_array($data)) {
            throw ProjectDefinitionParserException::invalidType('root', 'object');
        }

        /** @var array<string, mixed> $data */
        return $this->parseArray($data);
    }

    /** @param array<string, mixed> $data */
    public function parseArray(array $data): ProjectDefinition
    {
        if (! isset($data['project']) || ! is_array($data['project'])) {
            throw ProjectDefinitionParserException::missingField('project');
        }

        $project = $data['project'];
        $requiredProjectFields = ['name', 'slug', 'namespace'];
        foreach ($requiredProjectFields as $field) {
            if (! isset($project[$field]) || ! is_string($project[$field])) {
                throw ProjectDefinitionParserException::invalidType('project.'.$field, 'string');
            }
        }

        $identity = new ProjectIdentity($project['name'], $project['slug'], $project['namespace']);

        $modules = [];
        if (isset($data['modules'])) {
            if (! is_array($data['modules']) || ! array_is_list($data['modules'])) {
                throw ProjectDefinitionParserException::invalidType('modules', 'array of strings');
            }
            foreach ($data['modules'] as $module) {
                if (! is_string($module)) {
                    throw ProjectDefinitionParserException::invalidType('modules.*', 'string');
                }
                $modules[] = new ManifestDependency('package', $module, '*', true);
            }
        }

        $capabilities = [];
        if (isset($data['capabilities'])) {
            if (! is_array($data['capabilities']) || ! array_is_list($data['capabilities'])) {
                throw ProjectDefinitionParserException::invalidType('capabilities', 'array of strings');
            }
            foreach ($data['capabilities'] as $cap) {
                if (! is_string($cap)) {
                    throw ProjectDefinitionParserException::invalidType('capabilities.*', 'string');
                }
                $capabilities[] = new ManifestDependency('capability', $cap, '*', true);
            }
        }

        return new ProjectDefinition($identity, $modules, $capabilities);
    }
}
