<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\ValueObjects;

enum SettingType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
}
