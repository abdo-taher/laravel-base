<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Presentation\Cli;

use Base\Tooling\ProjectFactory\Public\ValueObjects\GenerationPlan;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\CopyTemplateOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\CopyTreeOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\CreateDirectoryOperation;

final class PlanRenderer
{
    public function renderText(GenerationPlan $plan): string
    {
        $output = [];
        $output[] = 'Project: '.$plan->identity->slug;
        $output[] = '';
        $output[] = 'Resolved Nodes:';

        $i = 1;
        foreach ($plan->resolvedGraph as $node) {
            $output[] = sprintf('%d. %-30s %s', $i++, $node->manifest->name, $node->reason->value);
        }

        $output[] = '';
        $output[] = 'Operations:';

        $j = 1;
        foreach ($plan->filesystemOperations as $op) {
            $output[] = sprintf('%d. %s', $j++, $op->description());
        }

        return implode("\n", $output)."\n";
    }

    public function renderJson(GenerationPlan $plan): string
    {
        $resolved = [];
        foreach ($plan->resolvedGraph as $node) {
            $resolved[] = [
                'name' => $node->manifest->name,
                'reason' => $node->reason->value,
            ];
        }

        $operations = [];
        foreach ($plan->filesystemOperations as $op) {
            if ($op instanceof CreateDirectoryOperation) {
                $operations[] = [
                    'type' => 'CreateDirectory',
                    'path' => $op->targetPath->value,
                ];
            } elseif ($op instanceof CopyTreeOperation) {
                $operations[] = [
                    'type' => 'CopyTree',
                    'source' => $op->sourcePackageName,
                    'path' => $op->targetPath->value,
                ];
            } elseif ($op instanceof CopyTemplateOperation) {
                $operations[] = [
                    'type' => 'CopyTemplate',
                    'template' => $op->template->value,
                    'path' => $op->targetPath->value,
                    'substitutions' => $op->substitutions,
                ];
            }
        }

        $data = [
            'project' => [
                'name' => $plan->identity->name,
                'slug' => $plan->identity->slug,
                'namespace' => $plan->identity->namespace,
            ],
            'resolved_nodes' => $resolved,
            'operations' => $operations,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }
}
