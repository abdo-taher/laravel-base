<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Security;

use Base\Foundation\Security\Public\Utilities\SecureCompare;
use Base\Foundation\Security\Public\ValueObjects\SensitiveValue;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SecurityPrimitivesTest extends TestCase
{
    public function test_sensitive_value_hides_content_in_string_cast(): void
    {
        $secret = new SensitiveValue('my-super-secret');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot cast SensitiveValue to string');
        (string) $secret;
    }

    public function test_sensitive_value_hides_content_in_json(): void
    {
        $secret = new SensitiveValue('my-super-secret');
        $json = json_encode(['token' => $secret]);

        self::assertIsString($json);
        self::assertStringContainsString('[HIDDEN]', $json);
        self::assertStringNotContainsString('my-super-secret', $json);
    }

    public function test_sensitive_value_hides_content_in_serialize(): void
    {
        $secret = new SensitiveValue('my-super-secret');
        $serialized = serialize($secret);

        self::assertStringContainsString('[HIDDEN]', $serialized);
        self::assertStringNotContainsString('my-super-secret', $serialized);
    }

    public function test_sensitive_value_prevents_unserialize(): void
    {
        $payload = 'O:59:"Base\Foundation\Security\Public\ValueObjects\SensitiveValue":1:{s:5:"value";s:15:"my-super-secret";}';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('SensitiveValue cannot be unserialized');
        unserialize($payload);
    }

    public function test_sensitive_value_reveals_when_explicitly_requested(): void
    {
        $secret = new SensitiveValue('my-super-secret');
        self::assertSame('my-super-secret', $secret->reveal());
    }

    public function test_secure_compare_works_identically_to_hash_equals(): void
    {
        self::assertTrue(SecureCompare::equals('known-hash', 'known-hash'));
        self::assertFalse(SecureCompare::equals('known-hash', 'different'));
        self::assertFalse(SecureCompare::equals('known-hash', 'known-has'));
    }
}
