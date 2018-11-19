<?php

namespace Caffeinated\Modules\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class BaseTestCase extends OrchestraTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Caffeinated\Modules\ModulesServiceProvider::class
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Module' => \Caffeinated\Modules\Facades\Module::class
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('view.paths', [__DIR__.'/resources/views']);
        $app['config']->set('modules.locations', [
            'modules' => [
                'driver' => 'local',
                'path' => base_path('modules'),
                'namespace' => 'Modules\\',
            ],
        ]);
    }

    public function tearDown()
    {
        foreach (config('modules.locations') as $locationConfig) {
            foreach (File::directories($locationConfig['path']) as $directory) {
                File::deleteDirectory($directory);
            }
        }

        parent::tearDown();
    }
}