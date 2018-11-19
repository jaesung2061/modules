<?php

namespace Caffeinated\Modules;

use Exception;

class ModuleRepositoriesFactory
{
    /**
     * @var \Caffeinated\Modules\Contracts\Repository[]
     */
    protected $repositories = [];

    public function location($location = null)
    {
        return $this->getModuleRepository($location);
    }

    public function boot()
    {
        foreach (array_keys(config('modules.locations')) as $location) {
            $repository = $this->getModuleRepository($location);

            $repository->boot();

            $this->repositories[$location] = $repository;
        }
    }

    public function repositories()
    {
        return $this->repositories;
    }

    protected function getModuleRepository($location = null)
    {
        $location = $location ?: $this->getDefaultLocation();
        $driverClass = $this->getRepositoryClass($location);

        if (! $driverClass) {
            throw new Exception("[$location] not found. Check your module locations configuration.");
        }

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
        $locationConfig = $this->getLocationConfig($location);

        if (is_null($locationConfig)) {
            throw new Exception("Location [$location] not configured. Please check your modules.php configuration.");
        }

        $driver = $locationConfig['driver'] ?? config('modules.default_driver');

        return config("modules.drivers.$driver");
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->getModuleRepository(), $method], $arguments);
    }
}
