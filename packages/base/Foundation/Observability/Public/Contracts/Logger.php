<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\Contracts;

use Base\Foundation\Observability\Public\ValueObjects\LogContext;
use Base\Foundation\Observability\Public\ValueObjects\ObservabilityContext;

/**
 * Operational logging boundary.
 *
 * Observability is best-effort by default. Concrete adapters are responsible
 * for isolating backend failures to ensure that operational logging failures
 * do not silently affect business outcomes. Do not add blanket catch(Throwable)
 * inside unrelated business code; failure isolation belongs in the adapters.
 *
 * Excludes Monolog, PSR-3, and framework logger types.
 * Bounded to specific explicit levels.
 */
interface Logger
{
    public function debug(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void;

    public function info(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void;

    public function warning(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void;

    public function error(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void;

    public function critical(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void;
}
