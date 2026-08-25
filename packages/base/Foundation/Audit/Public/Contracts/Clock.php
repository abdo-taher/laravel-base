<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Public\Contracts;

use DateTimeImmutable;

/**
 * An Audit-local clock for generating consistent timestamps.
 *
 * Avoids coupling to a generic SharedKernel clock unless a proven
 * cross-package need arises.
 *
 * No framework dependencies.
 */
interface Clock
{
    public function now(): DateTimeImmutable;
}
