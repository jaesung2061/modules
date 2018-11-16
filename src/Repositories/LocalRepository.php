<?php

namespace Caffeinated\Modules\Repositories;

class LocalRepository extends Repository
{
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

    /**
     * Get all modules.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function all()
    {
        return $this->modules->sortBy('order');
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

        $module = $this->modules->where('slug', $slug);

        return $module->get($key, $default);
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

        $content = json_encode($module->all(), JSON_PRETTY_PRINT);

        return $this->files->put($cachePath, $content);
    }

    /**
     * Get all enabled modules.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function enabled()
    {
        // TODO: Implement enabled() method.
    }

    /**
     * Get all disabled modules.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function disabled()
    {
        // TODO: Implement disabled() method.
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
        // TODO: Implement isEnabled() method.
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
        // TODO: Implement isDisabled() method.
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
        // TODO: Implement enable() method.
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
        // TODO: Implement disable() method.
    }
}
