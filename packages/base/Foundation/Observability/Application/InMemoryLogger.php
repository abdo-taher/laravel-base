<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Application;

use Base\Foundation\Observability\Public\Contracts\Logger;
use Base\Foundation\Observability\Public\ValueObjects\LogContext;
use Base\Foundation\Observability\Public\ValueObjects\ObservabilityContext;

/**
 * In-memory logger for B3.6 Foundation verification.
 *
 * Simulates a best-effort, fail-open logger.
 */
final class InMemoryLogger implements Logger
{
    /** @var list<array{level: string, message: string, context: ?LogContext, obs: ?ObservabilityContext}> */
    private array $logs = [];

    public function debug(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void
    {
        $this->logs[] = ['level' => 'debug', 'message' => $message, 'context' => $context, 'obs' => $observability];
    }

    public function info(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void
    {
        $this->logs[] = ['level' => 'info', 'message' => $message, 'context' => $context, 'obs' => $observability];
    }

    public function warning(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void
    {
        $this->logs[] = ['level' => 'warning', 'message' => $message, 'context' => $context, 'obs' => $observability];
    }

    public function error(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void
    {
        $this->logs[] = ['level' => 'error', 'message' => $message, 'context' => $context, 'obs' => $observability];
    }

    public function critical(string $message, ?LogContext $context = null, ?ObservabilityContext $observability = null): void
    {
        $this->logs[] = ['level' => 'critical', 'message' => $message, 'context' => $context, 'obs' => $observability];
    }

    /**
     * @return list<array{level: string, message: string, context: ?LogContext, obs: ?ObservabilityContext}>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    public function clear(): void
    {
        $this->logs = [];
    }
}
