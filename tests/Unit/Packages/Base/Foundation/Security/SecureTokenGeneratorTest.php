<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Security;

use Base\Foundation\Security\Application\NativeSecureTokenGenerator;
use PHPUnit\Framework\TestCase;

final class SecureTokenGeneratorTest extends TestCase
{
    public function test_native_generator_produces_raw_bytes(): void
    {
        $generator = new NativeSecureTokenGenerator;
        $token = $generator->generate(32);

        $raw = $token->reveal();
        self::assertSame(32, strlen($raw));
    }

    public function test_native_generator_produces_hex_strings(): void
    {
        $generator = new NativeSecureTokenGenerator;
        $token = $generator->generateHex(16);

        $hex = $token->reveal();
        // 16 bytes of entropy = 32 hex characters
        self::assertSame(32, strlen($hex));
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $hex);
    }
}
