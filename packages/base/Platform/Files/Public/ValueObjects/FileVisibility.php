<?php

declare(strict_types=1);

namespace Base\Platform\Files\Public\ValueObjects;

enum FileVisibility: string
{
    case PRIVATE = 'private';
    case PUBLIC = 'public';
}
