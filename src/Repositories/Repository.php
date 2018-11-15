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
     * @var array
     */
    protected $modules = [];

    /**
     * Constructor method.
     *
     * @param $location
     */
    public function __construct($location)
    {
        $this->location = $location;
        $this->modules = $this->collectManifests();
    }

    public function getPath()
    {
        return config("modules.locations.$this->location.path");
    }

    protected function collectManifests()
    {
        $manifests = [];

        foreach (File::directories($this->getPath()) as $moduleDirectory) {
            $manifest = $this->getManifest($moduleDirectory);

            $manifests[$manifest['slug']] = $manifest;
        }

        return $manifests;
    }

    public function getManifest($moduleDirectory)
    {
        $manifest = json_decode(File::get($moduleDirectory.'/module.json'), true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return collect($manifest);
        }

        throw new Exception("Your JSON manifest file in was not properly formatted. [$moduleDirectory]");
    }

//    /**
//     * Get a module's manifest contents.
//     *
//     * @param string $slug
//     *
//     * @return Collection|null
//     */
//    public function getManifest($slug)
//    {
//        $path     = $this->getManifestPath($slug);
//        $contents = $this->files->get($path);
//        $manifest = json_decode($contents, true);
//
//        if (json_last_error() === JSON_ERROR_NONE) {
//            return collect($manifest);
//        }
//
//        throw new Exception("[$slug] Your JSON manifest file was not properly formatted. Check for formatting issues and try again.");
//    }

//    /**
//     * Get all module basenames.
//     *
//     * @return array
//     */
//    protected function getAllBasenames()
//    {
//        try {
//            $collection = collect(app('storage')->directories($this->getPath()));
//
//            return $collection->map(function ($item, $key) {
//                return basename($item);
//            });
//        } catch (\InvalidArgumentException $e) {
//            return collect([]);
//        }
//    }

//    /**
//     * Set modules path in "RunTime" mode.
//     *
//     * @param string $path
//     *
//     * @return object $this
//     */
//    public function setPath($path)
//    {
//        $this->path = $path;
//
//        return $this;
//    }
//
//    /**
//     * Get path for the specified module.
//     *
//     * @param string $slug
//     *
//     * @return string
//     */
//    public function getModulePath($slug)
//    {
//        $module = studly_case(str_slug($slug));
//
//        if (\File::exists($this->getPath()."/{$module}/")) {
//            return $this->getPath()."/{$module}/";
//        }
//
//        return $this->getPath()."/{$slug}/";
//    }
//
//    /**
//     * Get path of module manifest file.
//     *
//     * @param $slug
//     *
//     * @return string
//     */
//    protected function getManifestPath($slug)
//    {
//        return $this->getModulePath($slug).'module.json';
//    }
//
//    /**
//     * Get modules namespace.
//     *
//     * @return string
//     */
//    public function getNamespace()
//    {
//        return rtrim($this->config->get('modules.namespace'), '/\\');
//    }
}
