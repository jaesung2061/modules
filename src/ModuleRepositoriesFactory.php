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
        return $this->repository($location);
    }

    public function boot()
    {
        foreach (array_keys(config('modules.locations')) as $location) {
            $repository = $this->repository($location);

            $repository->optimize();

            $this->repositories[$location] = $repository;
        }
    }

    /**
     * @return \Caffeinated\Modules\Contracts\Repository[]
     */
    public function repositories()
    {
        return $this->repositories;
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
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->repository(), $method], $arguments);
    }
}
