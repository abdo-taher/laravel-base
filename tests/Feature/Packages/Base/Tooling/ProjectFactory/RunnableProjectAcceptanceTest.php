<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Tooling\ProjectFactory;

use Tests\TestCase;

final class RunnableProjectAcceptanceTest extends TestCase
{
    private string $factoryPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factoryPath = base_path('bin/factory');
    }

    public function test_disposable_project_generation_and_execution(): void
    {
        $json = json_encode([
            'project' => [
                'name' => 'Acceptance Project',
                'slug' => 'acceptance-project',
                'namespace' => 'AcceptanceProject',
            ],
            'modules' => ['Modules.ReferenceCatalog'],
            'capabilities' => [],
        ]);

        $tmpJson = tempnam(sys_get_temp_dir(), 'acc-').'.json';

        file_put_contents($tmpJson, $json);

        $dest = sys_get_temp_dir().'/base-e2e-'.bin2hex(random_bytes(4));

        // 1. Generate
        $output = [];
        $return = 0;

        exec(
            "php {$this->factoryPath} generate $tmpJson --destination=$dest",
            $output,
            $return
        );

        self::assertSame(
            0,
            $return,
            'Factory generate failed: '.implode("\n", $output)
        );

        // 2. Validate Structure
        self::assertDirectoryExists($dest.'/app');
        self::assertDirectoryExists($dest.'/bootstrap');
        self::assertFileExists($dest.'/bootstrap/providers.php');
        self::assertFileExists($dest.'/composer.json');

        $providers = (string) file_get_contents(
            $dest.'/bootstrap/providers.php'
        );

        self::assertStringContainsString(
            'App\Providers\AppServiceProvider::class',
            $providers
        );

        self::assertStringContainsString(
            'Base\Platform\Files\FilesServiceProvider::class',
            $providers
        );

        self::assertStringContainsString(
            'Base\Platform\Media\MediaServiceProvider::class',
            $providers
        );

        self::assertStringContainsString(
            'Modules\ReferenceCatalog\ReferenceCatalogServiceProvider::class',
            $providers
        );

        // Unselected packages should not be generated.
        self::assertDirectoryDoesNotExist(
            $dest.'/packages/base/Platform/Verification'
        );

        self::assertStringNotContainsString(
            'VerificationServiceProvider',
            $providers
        );

        self::assertDirectoryDoesNotExist(
            $dest.'/packages/base/Tooling'
        );

        // 3. Composer Validate
        $output = [];
        $return = 0;

        exec(
            "cd $dest && composer validate --strict 2>&1",
            $output,
            $return
        );

        self::assertSame(
            0,
            $return,
            'Composer validate failed: '.implode("\n", $output)
        );

        // 4. Expensive execution (Tier 2)
        //
        // This section is explicitly opt-in.
        // It runs only when:
        //
        // RUN_EXPENSIVE_E2E=true
        //
        if (getenv('RUN_EXPENSIVE_E2E') === 'true') {
            // Install dependencies.
            $output = [];
            $return = 0;

            exec(
                "cd $dest && composer install --no-scripts --quiet",
                $output,
                $return
            );

            self::assertSame(
                0,
                $return,
                'Composer install failed: '.implode("\n", $output)
            );

            // Generate application key and create .env.
            $output = [];
            $return = 0;

            exec(
                "cd $dest && cp .env.example .env && php artisan key:generate",
                $output,
                $return
            );

            self::assertSame(
                0,
                $return,
                'Key generate failed: '.implode("\n", $output)
            );

            // Create SQLite database.
            touch($dest.'/database/database.sqlite');

            file_put_contents(
                $dest.'/.env',
                "\nDB_CONNECTION=sqlite\n",
                FILE_APPEND
            );

            // Run migrations.
            $output = [];
            $return = 0;

            exec(
                "cd $dest && php artisan migrate --force 2>&1",
                $output,
                $return
            );

            self::assertSame(
                0,
                $return,
                'Migration failed: '.implode("\n", $output)
            );

            // Verify generated routes.
            $output = [];
            $return = 0;

            exec(
                "cd $dest && php artisan route:list -vvv 2>&1",
                $output,
                $return
            );

            self::assertSame(
                0,
                $return,
                'Route list failed: '.implode("\n", $output)
            );

            $routes = implode("\n", $output);

            self::assertStringContainsString(
                'api/media',
                $routes
            );

            self::assertStringContainsString(
                'api/reference-items',
                $routes
            );
        }

        // 5. Cleanup
        exec("rm -rf $dest");

        unlink($tmpJson);
    }
}
