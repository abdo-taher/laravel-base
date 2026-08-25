<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Settings;

use Base\Platform\Settings\Public\Contracts\SettingContributor;
use Base\Platform\Settings\Public\Contracts\SettingsReader;
use Base\Platform\Settings\Public\Contracts\SettingsRegistry;
use Base\Platform\Settings\Public\Contracts\SettingsWriter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SettingsArchitectureTest extends TestCase
{
    public function test_public_contracts_have_no_framework_dependencies(): void
    {
        $contracts = [
            SettingsReader::class,
            SettingsWriter::class,
            SettingsRegistry::class,
            SettingContributor::class,
        ];

        foreach ($contracts as $contract) {
            $reflection = new ReflectionClass($contract);
            $fileName = $reflection->getFileName();

            if ($fileName === false) {
                $this->fail("Could not find file for contract {$contract}");
            }

            $content = file_get_contents($fileName);
            if ($content === false) {
                $this->fail("Could not read file for contract {$contract}");
            }

            $this->assertStringNotContainsString(
                'Illuminate\\',
                $content,
                "Contract {$contract} must not depend on Laravel framework."
            );
        }
    }
}
