<?php

namespace Caffeinated\Modules\Repositories;

use Exception;
use Caffeinated\Modules\Contracts\Repository as RepositoryContract;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

abstract class Repository implements RepositoryContract
{
    /**
     * Location config handle
     *
     * @var string
     */
    public $location;

    /**
     * @var string Path to the defined modules directory
     */
    protected $path;

    /**
     * Constructor method.
     * @param string $location
     */
    public function __construct(string $location)
    {
        $this->location = $location;
    }

    /**
     * Get all module basenames.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAllBasenames()
    {
        $path = $this->getPath();

        try {
            $collection = collect(File::directories($path));

            $basenames = $collection->map(function ($item, $key) {
                return basename($item);
            });

            return $basenames;
        } catch (\InvalidArgumentException $e) {
            return collect([]);
        }
    }

    /**
     * Get a module's manifest contents.
     *
     * @param string $slug
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getManifest($slug)
    {
        if (! is_null($slug)) {
            $path     = $this->getManifestPath($slug);
            $contents = File::get($path);
            $validate = @json_decode($contents, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $collection = collect(json_decode($contents, true));

                return $collection;
            }

            throw new Exception('['.$slug.'] Your JSON manifest file was not properly formatted. Check for formatting issues and try again.');
        }
    }

    /**
     * Get modules path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path ?: config("modules.locations.$this->location.path");
    }

    /**
     * Set modules path in "RunTime" mode.
     *
     * @param string $path
     *
     * @return object $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path for the specified module.
     *
     * @param string $slug
     *
     * @return string
     */
    public function getModulePath($slug)
    {
        $module = studly_case(str_slug($slug));

        if (\File::exists($this->getPath()."/{$module}/")) {
            return $this->getPath()."/{$module}/";
        }

        return $this->getPath()."/{$slug}/";
    }

    /**
     * Get path of module manifest file.
     *
     * @param $slug
     *
     * @return string
     */
    protected function getManifestPath($slug)
    {
        return $this->getModulePath($slug).'module.json';
    }

    /**
     * Get modules namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return rtrim(config("modules.locations.$this->location.namespace"), '/\\');
    }
}