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

            // add base namespace to manifest
            $manifest['id'] = crc32($manifest->get('slug'));
            $manifest['basename'] = $basename = $this->getModuleNamespace($moduleDirectory);
            $manifest['order'] = $manifest['order'] ?? 9999;
            $manifest['enabled'] = $manifest['enabled'] ?? config('modules.enabled');

            $modules->push($manifest);
        };

        $this->modules = $modules->sort(function ($a, $b) {
            $a = $a['order'];
            $b = $b['order'];

            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        foreach ($this->all() as $module) {
            $this->registerServiceProvider($module);

            foreach ($module['autoload'] ?? [] as $file) {
                $basePath = config("modules.locations.$this->location.path");
                $filePath = "$basePath/$basename/$file";

                if (File::exists($filePath)) {
                    require $filePath;
                }
            }
        }
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
     * @param $moduleDirectory
     * @return void
     */
    protected function registerServiceProvider($module)
    {
        $locationNamespace = trim(config("modules.locations.$this->location.namespace"), '\\');
        $serviceProvider = $locationNamespace.'\\'.$module['basename'].'\\'.config('modules.provider_class');

        if (class_exists($serviceProvider)) {
            app()->register($serviceProvider);
        }
    }

    /**
     * @param $moduleDirectory
     * @return string
     */
    protected function getModuleNamespace($moduleDirectory)
    {
        return trim(array_last(explode(DIRECTORY_SEPARATOR, $moduleDirectory)), '\\');
    }
}
