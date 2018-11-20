<?php

namespace Caffeinated\Modules;

use Caffeinated\Modules\Contracts\Repository;
use Caffeinated\Modules\Exceptions\ModuleNotFoundException;
use Exception;
use Illuminate\Foundation\Application;

class ModuleRepositoriesManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @return \Caffeinated\Modules\Contracts\Repository[]
     */
    protected $repositories = [];

    /**
     * Create a new Modules instance.
     *
     * @param Application $app
     * @param Repository  $repository
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function location($location = null)
    {
        return $this->repository($location);
    }

    /**
     * @return \Caffeinated\Modules\Contracts\Repository[]
     */
    public function repositories()
    {
        return $this->repositories;
    }

    /**
     * Register the module service provider file from all modules.
     *
     * @return void
     */
    public function register()
    {
        foreach (array_keys(config('modules.locations')) as $location) {
            $modules = $this->repository($location)->enabled();

            $modules->each(function ($module) {
                try {
                    $this->registerServiceProvider($module);

                    $this->autoloadFiles($module);
                } catch (ModuleNotFoundException $e) {
                    //
                }
            });
        }


    }

    /**
     * @param string $location
     * @return \Caffeinated\Modules\Contracts\Repository
     * @throws \Exception
     */
    protected function repository($location = null)
    {
        $location = $location ?: $this->defaultLocation();
        $driverClass = $this->repositoryClass($location);

        if (! $driverClass) {
            throw new Exception("[$location] not found. Check your module locations configuration.");
        }

        return $this->repositories[$location] ?? new $driverClass($location);
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function defaultLocation()
    {
        return config('modules.default_location');
    }

    /**
     * @param $location
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function locationConfig($location)
    {
        return config("modules.locations.$location");
    }

    /**
     * @param $location
     * @return \Illuminate\Config\Repository|mixed
     * @throws \Exception
     */
    protected function repositoryClass($location)
    {
        $locationConfig = $this->locationConfig($location);

        if (is_null($locationConfig)) {
            throw new Exception("Location [$location] not configured. Please check your modules.php configuration.");
        }

        $driver = $locationConfig['driver'] ?? config('modules.default_driver');

        return config("modules.drivers.$driver");
    }

    /**
     * Register the module service provider.
     *
     * @param array $module
     *
     * @return void
     */
    private function registerServiceProvider($module)
    {
        $serviceProvider = module_class($module['slug'], config('modules.provider_class', 'Providers\\ModuleServiceProvider'));

        if (class_exists($serviceProvider)) {
            $this->app->register($serviceProvider);
        }
    }

    /**
     * Autoload custom module files.
     *
     * @param array $module
     *
     * @return void
     */
    private function autoloadFiles($module)
    {
        if (isset($module['autoload'])) {
            foreach ($module['autoload'] as $file) {
                $path = module_path($module['slug'], $file);

                if (file_exists($path)) {
                    include $path;
                }
            }
        }
    }

    /**
     * Oh sweet sweet magical method.
     *
     * @param string $method
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->repository(), $method], $arguments);
    }
}
