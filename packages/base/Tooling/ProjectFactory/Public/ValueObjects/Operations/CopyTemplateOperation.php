<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects\Operations;

use Base\Tooling\ProjectFactory\Public\ValueObjects\SafePath;
use Base\Tooling\ProjectFactory\Public\ValueObjects\TemplateReference;

final readonly class CopyTemplateOperation implements PlannedOperation
{
    /**
     * @param  array<string, string>  $substitutions
     */
    public function __construct(
        public TemplateReference $template,
        public SafePath $targetPath,
        public array $substitutions = [],
    ) {}

    public function description(): string
    {
        return sprintf('Copy template %s to %s', $this->template->value, $this->targetPath->value);
    }
}
