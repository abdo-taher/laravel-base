<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function test_string_normalization_behaves_as_expected(): void
    {
        $value = 'Base Platform';

        $normalized = strtolower(str_replace(' ', '-', $value));

        $this->assertSame('base-platform', $normalized);
    }
}
