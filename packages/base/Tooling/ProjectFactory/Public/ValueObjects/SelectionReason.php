<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects;

enum SelectionReason: string
{
    case DIRECT_MODULE = 'direct-module';
    case DIRECT_CAPABILITY = 'direct-capability';
    case AUTO_RESOLVED = 'auto-resolved';
}
