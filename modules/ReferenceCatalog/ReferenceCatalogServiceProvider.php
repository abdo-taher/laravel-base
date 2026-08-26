<?php

declare(strict_types=1);

namespace Modules\ReferenceCatalog;

use Illuminate\Support\ServiceProvider;

final class ReferenceCatalogServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Presentation/Routes/api.php');
    }
}
