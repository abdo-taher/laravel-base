<?php

declare(strict_types=1);

namespace Base\Platform\Media;

use Base\Platform\Media\Application\MediaApplicationService;
use Base\Platform\Media\Presentation\Contracts\MediaAccessScopeResolver;
use Base\Platform\Media\Presentation\Http\Resolvers\RequestMediaAccessScopeResolver;
use Base\Platform\Media\Public\Contracts\MediaCleaner;
use Base\Platform\Media\Public\Contracts\MediaSynchronizer;
use Base\Platform\Media\Public\Contracts\MediaUploader;
use Illuminate\Support\ServiceProvider;

final class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaApplicationService::class);
        $this->app->bind(MediaUploader::class, MediaApplicationService::class);
        $this->app->bind(MediaSynchronizer::class, MediaApplicationService::class);
        $this->app->bind(MediaCleaner::class, MediaApplicationService::class);

        $this->app->bind(
            MediaAccessScopeResolver::class,
            RequestMediaAccessScopeResolver::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Presentation/Routes/api.php');
    }
}
