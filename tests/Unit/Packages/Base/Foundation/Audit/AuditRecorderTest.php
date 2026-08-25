<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Audit;

use Base\Foundation\Audit\Application\InMemoryAuditRecorder;
use Base\Foundation\Audit\Public\Contracts\AuditRecorder;
use Base\Foundation\Audit\Public\Exceptions\AuditRecordingFailed;
use Base\Foundation\Audit\Public\ValueObjects\Action;
use Base\Foundation\Audit\Public\ValueObjects\AuditEvent;
use Base\Foundation\Identity\Public\ValueObjects\Principal;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AuditRecorderTest extends TestCase
{
    public function test_in_memory_recorder_stores_and_retrieves_events(): void
    {
        $recorder = new InMemoryAuditRecorder;

        $event = new AuditEvent(
            eventId: 'evt-1',
            action: new Action('test.action'),
            timestamp: new DateTimeImmutable,
            principal: new Principal(new PrincipalId('usr-1'), PrincipalType::user())
        );

        $recorder->record($event);

        $events = $recorder->getRecordedEvents();
        self::assertCount(1, $events);
        self::assertSame($event, $events[0]);
    }

    public function test_in_memory_recorder_can_be_cleared(): void
    {
        $recorder = new InMemoryAuditRecorder;

        $recorder->record(new AuditEvent('evt-1', new Action('a'), new DateTimeImmutable));
        self::assertCount(1, $recorder->getRecordedEvents());

        $recorder->clear();
        self::assertCount(0, $recorder->getRecordedEvents());
    }

    public function test_audit_recording_failed_exception_does_not_leak_details(): void
    {
        $exception = AuditRecordingFailed::forEvent('evt-1', new \Exception('Secret DB password'));

        self::assertStringContainsString('evt-1', $exception->getMessage());
        self::assertStringNotContainsString('Secret DB password', $exception->getMessage());
    }

    public function test_caller_can_handle_recorder_failure_explicitly(): void
    {
        // Simulate a failing recorder
        $failingRecorder = new class implements AuditRecorder
        {
            public function record(AuditEvent $event): void
            {
                throw AuditRecordingFailed::forEvent($event->eventId, new \Exception('Disk full'));
            }
        };

        $event = new AuditEvent('evt-fail', new Action('test'), new DateTimeImmutable);

        $handled = false;
        try {
            $failingRecorder->record($event);
        } catch (AuditRecordingFailed $e) {
            // The caller is in control. They can swallow, retry, or rethrow.
            $handled = true;
            self::assertStringContainsString('evt-fail', $e->getMessage());
        }

        self::assertTrue($handled, 'The caller should be able to catch and handle the explicit failure.');
    }
}
