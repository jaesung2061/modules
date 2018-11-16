<?php

namespace Caffeinated\Modules\Repositories;

use Exception;
use Caffeinated\Modules\Contracts\Repository as RepositoryContract;
use Illuminate\Support\Facades\File;

abstract class Repository implements RepositoryContract
{
    /**
     * @var string
     */
    protected $location;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $modules;

    /**
     * Constructor method.
     *
     * @param $location
     */
    public function __construct($location)
    {
        $this->location = $location;
    }

    public function getPath()
    {
        return config("modules.locations.$this->location.path");
    }

    public function boot()
    {
        $modules = collect();

        foreach (File::directories($this->getPath()) as $moduleDirectory) {
            $manifest = $this->getManifest($moduleDirectory);

            $modules->put($manifest['slug'], $manifest);

            $this->registerServiceProvider($moduleDirectory);
        };

        $this->modules = $modules;
    }

    public function getManifest($moduleDirectory)
    {
        $manifest = json_decode(File::get($moduleDirectory.'/module.json'), true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return collect($manifest);
        }

        throw new Exception("Your JSON manifest file in was not properly formatted. [$moduleDirectory]");
    }

    /**
     * Register the module service provider.
     *
     * @param array $module
     *
     * @return void
     */
    protected function registerServiceProvider($moduleDirectory)
    {
        $locationNamespace = trim(config("modules.locations.$this->location.namespace"), '\\');
        $moduleNamespace = trim(array_last(explode(DIRECTORY_SEPARATOR, $moduleDirectory)), '\\');
        $serviceProvider = $locationNamespace.'\\'.$moduleNamespace.'\\'.config('modules.provider_class');

        if (class_exists($serviceProvider)) {
            app()->register($serviceProvider);
        }
    }
}
