<?php

declare(strict_types=1);

namespace Base\Foundation\Security;

use Base\Foundation\Security\Application\NativeSecureTokenGenerator;
use Base\Foundation\Security\Public\Contracts\SecureTokenGenerator;
use Illuminate\Support\ServiceProvider;

/**
 * Security Foundation service provider.
 */
final class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SecureTokenGenerator::class, NativeSecureTokenGenerator::class);
    }
}
