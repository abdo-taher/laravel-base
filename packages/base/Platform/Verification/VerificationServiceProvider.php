<?php

declare(strict_types=1);

namespace Base\Platform\Verification;

use Base\Platform\Verification\Application\DatabaseVerificationService;
use Base\Platform\Verification\Public\Contracts\VerificationIssuer;
use Base\Platform\Verification\Public\Contracts\VerificationVerifier;
use Illuminate\Support\ServiceProvider;

final class VerificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseVerificationService::class);
        $this->app->bind(VerificationIssuer::class, DatabaseVerificationService::class);
        $this->app->bind(VerificationVerifier::class, DatabaseVerificationService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
    }
}
