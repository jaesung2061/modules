<?php

namespace Caffeinated\Modules\Repositories;

class LocalRepository extends Repository
{
    /**
     * Get all modules.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function all()
    {
        return $this->modules;
    }

    /**
     * Get all module slugs.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function slugs()
    {
        return $this->modules->pluck('slug');
    }

    /**
     * Get modules based on where clause.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function where($key, $value)
    {
        return $this->modules->where($key, $value)->first();
    }

    /**
     * Sort modules by given key in ascending order.
     *
     * @param string $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function sortBy($key)
    {
        return $this->modules->sortBy($key);
    }

    /**
     * Sort modules by given key in ascending order.
     *
     * @param string $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function sortByDesc($key)
    {
        return $this->modules->sortBy($key, 'desc');
    }

    /**
     * Determines if the given module exists.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function exists($slug)
    {
        return $this->slugs()->contains($slug) || $this->slugs()->contains(str_slug($slug));
    }

    /**
     * Returns a count of all modules.
     *
     * @return int
     */
    public function count()
    {
        return $this->modules->count();
    }

    /**
     * Returns the given module property.
     *
     * @param string $property
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get($property, $default = null)
    {
        list($slug, $key) = explode('::', $property);

        return $this->modules->where('slug', $slug)->first()->get($key, $default);
    }

    /**
     * Set the given module property value.
     *
     * @param string $property
     * @param mixed $value
     *
     * @return bool
     */
    public function set($property, $value)
    {
        list($slug, $key) = explode('::', $property);

        $module = $this->modules->where('slug', $slug);

        if (isset($module[$key])) {
            unset($module[$key]);
        }

        $module[$key] = $value;
    }

    /**
     * Get all enabled modules.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function enabled()
    {
        return $this->modules->where('enabled', true);
    }

    /**
     * Get all disabled modules.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function disabled()
    {
        return $this->modules->where('enabled', false);
    }

    /**
     * Determines if the specified module is enabled.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function isEnabled($slug)
    {
        $module = $this->modules->where('slug', $slug)->first();

        return !!$module->get('enabled');
    }

    /**
     * Determines if the specified module is disabled.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function isDisabled($slug)
    {
        $module = $this->modules->where('slug', $slug)->first();

        return !!$module->get('enabled');
    }

    public function getModulePath($slug)
    {
        return config("modules.locations.$this->location.path").'/'.$this->where('slug', $slug)['basename'];
    }

    /**
     * Enables the specified module.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function enable($slug)
    {
        return $this->set($slug.'::enabled', true);
    }

    /**
     * Disables the specified module.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function disable($slug)
    {
        return $this->set($slug.'::enabled', true);
    }

    /**
     * Get all module manifest properties and store
     * in the respective container.
     *
     * @return bool
     */
    public function optimize()
    {
        //
    }
}
