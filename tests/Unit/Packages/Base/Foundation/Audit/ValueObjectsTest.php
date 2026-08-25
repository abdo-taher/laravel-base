<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Audit;

use Base\Foundation\Audit\Application\SystemClock;
use Base\Foundation\Audit\Public\ValueObjects\Action;
use Base\Foundation\Audit\Public\ValueObjects\AuditEvent;
use Base\Foundation\Audit\Public\ValueObjects\Metadata;
use Base\Foundation\Audit\Public\ValueObjects\SubjectRef;
use Base\Foundation\Identity\Public\ValueObjects\Principal;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalType;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    // ── Action ─────────────────────────────────────────────────────────────

    public function test_action_wraps_non_empty_string(): void
    {
        $a = new Action('module.action');
        self::assertSame('module.action', $a->value);
        self::assertSame('module.action', $a->toString());
    }

    public function test_action_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Action('');
    }

    public function test_action_equality(): void
    {
        $a = new Action('a');
        $b = new Action('a');
        $c = new Action('c');
        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }

    // ── SubjectRef ─────────────────────────────────────────────────────────

    public function test_subject_ref_wraps_strings(): void
    {
        $s = new SubjectRef('user', 'usr-1');
        self::assertSame('user', $s->type);
        self::assertSame('usr-1', $s->id);
    }

    public function test_subject_ref_rejects_empty_strings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SubjectRef('', '123');
    }

    public function test_subject_ref_rejects_empty_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SubjectRef('user', '');
    }

    public function test_subject_ref_equality(): void
    {
        $a = new SubjectRef('user', '1');
        $b = new SubjectRef('user', '1');
        $c = new SubjectRef('user', '2');
        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }

    // ── Metadata ───────────────────────────────────────────────────────────

    public function test_metadata_accepts_scalars_and_nulls(): void
    {
        $m = new Metadata([
            'string' => 'val',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
            'array' => ['nested' => 1],
        ]);

        self::assertArrayHasKey('string', $m->values);
    }

    public function test_metadata_rejects_objects(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid metadata value at path "obj"');

        new Metadata(['obj' => new \stdClass]);
    }

    public function test_metadata_rejects_nested_objects(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid metadata value at path "nested.obj"');

        new Metadata(['nested' => ['obj' => new \stdClass]]);
    }

    public function test_metadata_rejects_non_string_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only string keys are allowed in metadata arrays');

        new Metadata(['valid' => 1, 0 => 'invalid']); // @phpstan-ignore-line
    }

    public function test_metadata_rejects_resources(): void
    {
        $resource = fopen('php://memory', 'r');
        self::assertIsResource($resource);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid metadata value at path "res"');

        try {
            new Metadata(['res' => $resource]);
        } finally {
            fclose($resource);
        }
    }

    public function test_metadata_rejects_closures(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid metadata value at path "closure"');

        new Metadata(['closure' => function () {}]);
    }

    // ── AuditEvent ─────────────────────────────────────────────────────────

    public function test_audit_event_stores_properties(): void
    {
        $now = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $principal = new Principal(new PrincipalId('usr-1'), PrincipalType::user());
        $action = new Action('test');

        $event = new AuditEvent(
            eventId: 'evt-1',
            action: $action,
            timestamp: $now,
            principal: $principal,
        );

        self::assertSame('evt-1', $event->eventId);
        self::assertSame($action, $event->action);
        self::assertSame($now, $event->timestamp);
        self::assertSame($principal, $event->principal);
        self::assertNull($event->subject);
        self::assertNull($event->metadata);
    }

    public function test_audit_event_rejects_empty_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AuditEvent('', new Action('a'), new DateTimeImmutable);
    }

    public function test_audit_event_rejects_whitespace_only_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Event ID must be a non-empty string.');
        new AuditEvent('   ', new Action('a'), new DateTimeImmutable);
    }

    // ── SystemClock ────────────────────────────────────────────────────────

    public function test_system_clock_returns_utc_datetime_immutable(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();

        self::assertInstanceOf(DateTimeImmutable::class, $now);
        self::assertSame('UTC', $now->getTimezone()->getName());
    }
}
