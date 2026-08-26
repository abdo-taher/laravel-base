<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\Contracts;

use Base\Tooling\ProjectFactory\Public\Exceptions\ProjectMaterializationFailed;
use Base\Tooling\ProjectFactory\Public\ValueObjects\FactoryExecutionResult;
use Base\Tooling\ProjectFactory\Public\ValueObjects\GenerationPlan;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDestination;

interface ProjectMaterializer
{
    /**
     * @throws ProjectMaterializationFailed
     */
    public function materialize(GenerationPlan $plan, ProjectDestination $destination): FactoryExecutionResult;
}
