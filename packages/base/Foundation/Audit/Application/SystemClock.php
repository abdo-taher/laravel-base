<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Application;

use Base\Foundation\Audit\Public\Contracts\Clock;
use DateTimeImmutable;

/**
 * Standard system clock for the Audit module.
 *
 * Always returns the current time in UTC.
 */
final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
