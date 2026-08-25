<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Media;

use Base\Platform\Media\Public\Exceptions\InvalidMediaReference;
use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Base\Platform\Media\Public\ValueObjects\MediaOwnerReference;
use Base\Platform\Media\Public\ValueObjects\MediaReference;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_valid_media_reference(): void
    {
        $ref = MediaReference::fromString('med_123abc');
        $this->assertSame('med_123abc', $ref->value);
    }

    public function test_invalid_media_reference(): void
    {
        $this->expectException(InvalidMediaReference::class);
        MediaReference::fromString('invalid_123');
    }

    public function test_valid_access_scope(): void
    {
        $scope = MediaAccessScope::fromString('user_123');
        $this->assertSame('user_123', $scope->value);
    }

    public function test_empty_access_scope(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MediaAccessScope::fromString(' ');
    }

    public function test_valid_owner_reference(): void
    {
        $owner = new MediaOwnerReference('product', '123');
        $this->assertSame('product', $owner->type);
        $this->assertSame('123', $owner->id);
    }
}
