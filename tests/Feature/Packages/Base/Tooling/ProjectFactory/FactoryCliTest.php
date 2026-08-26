<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Tooling\ProjectFactory;

use Tests\TestCase;

final class FactoryCliTest extends TestCase
{
    private string $factoryPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factoryPath = base_path('bin/factory');
    }

    public function test_cli_help(): void
    {
        $output = [];
        $return = 0;
        exec("php {$this->factoryPath} --help", $output, $return);

        self::assertSame(0, $return);
        self::assertStringContainsString('Usage:', implode("\n", $output));
    }

    public function test_plan_with_reference_catalog(): void
    {
        $json = json_encode([
            'project' => [
                'name' => 'Test Project',
                'slug' => 'test-project',
                'namespace' => 'TestProject',
            ],
            'modules' => ['Modules.ReferenceCatalog'],
            'capabilities' => [],
        ]);

        $tmpJson = tempnam(sys_get_temp_dir(), 'plan-').'.json';
        file_put_contents($tmpJson, $json);

        $output = [];
        $return = 0;
        exec("php {$this->factoryPath} plan $tmpJson", $output, $return);

        self::assertSame(0, $return);

        $outStr = implode("\n", $output);
        self::assertStringContainsString('Base.Platform.Files', $outStr);
        self::assertStringContainsString('Base.Platform.Media', $outStr);
        self::assertStringContainsString('Modules.ReferenceCatalog', $outStr);
        self::assertStringContainsString('direct-module', $outStr);
        self::assertStringContainsString('auto-resolved', $outStr);

        unlink($tmpJson);
    }

    public function test_plan_with_direct_capability(): void
    {
        $json = json_encode([
            'project' => [
                'name' => 'Test',
                'slug' => 'test',
                'namespace' => 'Test',
            ],
            'capabilities' => ['media.attachments'],
        ]);

        $tmpJson = tempnam(sys_get_temp_dir(), 'plan-').'.json';
        file_put_contents($tmpJson, $json);

        $output = [];
        $return = 0;
        exec("php {$this->factoryPath} plan $tmpJson", $output, $return);

        self::assertSame(0, $return);

        $outStr = implode("\n", $output);
        self::assertStringContainsString('Base.Platform.Media', $outStr);
        self::assertStringContainsString('direct-capability', $outStr);

        unlink($tmpJson);
    }

    public function test_generate_from_different_cwd(): void
    {
        $json = json_encode([
            'project' => [
                'name' => 'Test Gen',
                'slug' => 'test-gen',
                'namespace' => 'TestGen',
            ],
            'modules' => ['Modules.ReferenceCatalog'],
        ]);

        $tmpJson = tempnam(sys_get_temp_dir(), 'gen-').'.json';
        file_put_contents($tmpJson, $json);

        $dest = sys_get_temp_dir().'/base-cli-test-'.bin2hex(random_bytes(4));

        // Execute from /tmp explicitly
        $output = [];
        $return = 0;
        exec("cd /tmp && php {$this->factoryPath} generate $tmpJson --destination=$dest", $output, $return);

        self::assertSame(0, $return);

        self::assertDirectoryExists($dest.'/packages/base/Platform/Files');
        self::assertDirectoryExists($dest.'/packages/base/Platform/Media');
        self::assertDirectoryExists($dest.'/modules/ReferenceCatalog');

        // Output should not contain paths
        $outStr = implode("\n", $output);
        self::assertStringContainsString('Generated project "test-gen"', $outStr);
        self::assertStringContainsString('Destination: '.$dest, $outStr);
        self::assertStringNotContainsString('packages/base/Platform/Media', $outStr);

        // Cleanup
        exec("rm -rf $dest");
        unlink($tmpJson);
    }

    public function test_invalid_json(): void
    {
        $tmpJson = tempnam(sys_get_temp_dir(), 'plan-').'.json';
        file_put_contents($tmpJson, '{ invalid }');

        $output = [];
        $return = 0;
        exec("php {$this->factoryPath} plan $tmpJson 2>&1", $output, $return);

        self::assertSame(2, $return);
        self::assertStringContainsString('Input Error: Invalid JSON definition', implode("\n", $output));

        unlink($tmpJson);
    }

    public function test_unknown_module(): void
    {
        $json = json_encode([
            'project' => [
                'name' => 'Test',
                'slug' => 'test',
                'namespace' => 'Test',
            ],
            'modules' => ['Modules.DoesNotExist'],
        ]);

        $tmpJson = tempnam(sys_get_temp_dir(), 'plan-').'.json';
        file_put_contents($tmpJson, $json);

        $output = [];
        $return = 0;
        exec("php {$this->factoryPath} plan $tmpJson 2>&1", $output, $return);

        self::assertSame(3, $return); // CODE_PLAN_ERROR
        self::assertStringContainsString('Planning Error: Unknown explicit module', implode("\n", $output));

        unlink($tmpJson);
    }

    public function test_destination_exists(): void
    {
        $json = json_encode([
            'project' => [
                'name' => 'Test',
                'slug' => 'test',
                'namespace' => 'Test',
            ],
        ]);

        $tmpJson = tempnam(sys_get_temp_dir(), 'gen-').'.json';
        file_put_contents($tmpJson, $json);

        $dest = sys_get_temp_dir().'/base-cli-test-'.bin2hex(random_bytes(4));
        mkdir($dest); // Pre-create

        $output = [];
        $return = 0;
        exec("php {$this->factoryPath} generate $tmpJson --destination=$dest 2>&1", $output, $return);

        self::assertSame(4, $return); // CODE_MATERIALIZE_ERROR
        self::assertStringContainsString('Materialization Error: Destination already exists', implode("\n", $output));

        rmdir($dest);
        unlink($tmpJson);
    }
}
