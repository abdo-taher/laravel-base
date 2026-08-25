<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\Contracts;

/**
 * Passive extension contract for modules to contribute settings.
 */
interface SettingContributor
{
    /**
     * Contribute setting definitions to the registry.
     */
    public function contribute(SettingsRegistry $registry): void;
}
