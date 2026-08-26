<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Verification;

use Base\Platform\Verification\Public\ValueObjects\VerificationPurpose;
use Base\Platform\Verification\Public\ValueObjects\VerificationReference;
use Base\Platform\Verification\Public\ValueObjects\VerificationTarget;
use PHPUnit\Framework\TestCase;

class ValueObjectsTest extends TestCase
{
    public function test_target(): void
    {
        $target = new VerificationTarget('email', 'test@test.com');
        $this->assertSame('email', $target->type);
        $this->assertSame('test@test.com', $target->key);
    }

    public function test_purpose(): void
    {
        $purpose = new VerificationPurpose('auth.login');
        $this->assertSame('auth.login', $purpose->value);
    }

    public function test_reference(): void
    {
        $ref = VerificationReference::generate();
        $this->assertStringStartsWith('ver_', $ref->value);
    }
}
