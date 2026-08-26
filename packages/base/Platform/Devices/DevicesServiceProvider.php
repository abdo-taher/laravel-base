<?php

declare(strict_types=1);

namespace Base\Platform\Devices;

use Base\Platform\Devices\Application\DatabaseDeviceRegistry;
use Base\Platform\Devices\Public\Contracts\DeviceRegistry;
use Illuminate\Support\ServiceProvider;

final class DevicesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseDeviceRegistry::class);
        $this->app->bind(DeviceRegistry::class, DatabaseDeviceRegistry::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
    }
}
