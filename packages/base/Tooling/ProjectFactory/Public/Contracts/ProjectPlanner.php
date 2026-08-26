<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\Contracts;

use Base\Tooling\ProjectFactory\Public\Exceptions\ProjectPlannerException;
use Base\Tooling\ProjectFactory\Public\ValueObjects\GenerationPlan;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDefinition;

interface ProjectPlanner
{
    /**
     * @throws ProjectPlannerException
     */
    public function plan(ProjectDefinition $definition): GenerationPlan;
}
