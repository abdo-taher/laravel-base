<?php

declare(strict_types=1);

namespace Base\Platform\Settings;

use Base\Platform\Settings\Application\InMemorySettingsRegistry;
use Base\Platform\Settings\Infrastructure\Database\DatabaseSettingsRepository;
use Base\Platform\Settings\Public\Contracts\SettingsReader;
use Base\Platform\Settings\Public\Contracts\SettingsRegistry;
use Base\Platform\Settings\Public\Contracts\SettingsRepository;
use Base\Platform\Settings\Public\Contracts\SettingsWriter;
use Illuminate\Support\ServiceProvider;

final class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsRegistry::class, InMemorySettingsRegistry::class);

        $this->app->singleton(SettingsRepository::class, DatabaseSettingsRepository::class);
        $this->app->bind(SettingsReader::class, SettingsRepository::class);
        $this->app->bind(SettingsWriter::class, SettingsRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
    }
}
