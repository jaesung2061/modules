<?php

use Caffeinated\Modules\Exceptions\ModuleNotFoundException;

if (!function_exists('modules')) {
    /**
     * Get modules repository.
     *
     * @param string $location
     * @return mixed
     */
    function modules($location = null) {
        if ($location) {
            return app('modules')->location($location);
        }

        return app('modules');
    }
}

if (!function_exists('module_path')) {
    /**
     * Return the path to the given module file.
     *
     * @param string $slug
     * @param string $file
     *
     * @param null $location
     * @return string
     * @throws \Caffeinated\Modules\Exceptions\ModuleNotFoundException
     */
    function module_path($slug = null, $file = '', $location = null)
    {
        $modulesPath = module_location_config($location);
        $pathMap = config('modules.pathMap');

        if (!empty($file) && !empty($pathMap)) {
            $file = str_replace(
                array_keys($pathMap),
                array_values($pathMap),
                $file
            );
        }

        $filePath = $file ? '/' . ltrim($file, '/') : '';

        if (is_null($slug)) {
            if (empty($file)) {
                return $modulesPath;
            }

            return $modulesPath . $filePath;
        }

        $module = Module::where('slug', $slug);

        if (is_null($module)) {
            throw new ModuleNotFoundException($slug);
        }

        return $modulesPath . '/' . $module['basename'] . $filePath;
    }
}

if (!function_exists('module_class')) {
    /**
     * Return the full path to the given module class.
     *
     * @param string $slug
     * @param string $class
     *
     * @return string
     * @throws \Caffeinated\Modules\Exceptions\ModuleNotFoundException
     */
    function module_class($slug, $class)
    {
        $module = Module::where('slug', $slug);

        if (is_null($module)) {
            throw new ModuleNotFoundException($slug);
        }

        $namespace = config('modules.namespace') . $module['basename'];

        return "{$namespace}\\{$class}";
    }
}

/**
 * @param string $location
 * @param string $key
 * @return \Illuminate\Config\Repository|mixed
 */
function module_location_config($location = null, $key = null)
{
    $location = $location ?: config('modules.default_location');

    if ($key) {
        return config("modules.locations.$location.$key");
    }

    return config("modules.locations.$location");
}
