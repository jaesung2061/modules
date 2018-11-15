<?php

namespace Caffeinated\Modules;

class ModuleRepositoriesFactory
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \Caffeinated\Modules\Contracts\Repository[]
     */
    protected $repositories = [];

    /**
     * ModulesFactory constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function boot()
    {
        foreach (array_keys(config('modules.locations')) as $location) {
            $this->repositories[$location] = $this->getModuleRepository();
        }
    }

    public function getModuleRepository($location = null)
    {
        $location = $location ?: $this->getDefaultLocation();
        $driverClass = $this->getRepositoryClass($location);

        return $this->repositories[$location] ?? new $driverClass($location);
    }

    protected function getDefaultLocation()
    {
        return config('modules.default_location');
    }

    protected function getLocationConfig($location)
    {
        return config("modules.locations.$location");
    }

    protected function getRepositoryClass($location)
    {
        $driver = $this->getLocationConfig($location)['driver']
            ?? config('modules.default_driver');

        return config("modules.drivers.$driver");
    }
}
