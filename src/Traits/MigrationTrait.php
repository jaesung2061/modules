<?php

namespace Caffeinated\Modules\Traits;

trait MigrationTrait
{
    /**
     * Require (once) all migration files for the supplied module.
     *
     * @param string $module
     * @param string $location
     */
    protected function requireMigrations($module, $location = null)
    {
        $path = $this->getMigrationPath($module, $location);

        $migrations = $this->laravel['files']->glob($path.'*_*.php');

        foreach ($migrations as $migration) {
            $this->laravel['files']->requireOnce($migration);
        }
    }

    /**
     * Get migration directory path.
     *
     * @param string $module
     * @param string $location
     *
     * @return string
     */
    protected function getMigrationPath($module, $location = null)
    {
        return module_path($module, 'Database/Migrations', $location);
    }
}
