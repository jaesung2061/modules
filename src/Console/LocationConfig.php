<?php

namespace Caffeinated\Modules\Console;

trait LocationConfig
{
    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getLocationConfig($key = null)
    {
        $location = $this->option('location') ?: config('modules.default_location');

        if ($key) {
            return config("modules.$location.$key");
        }

        return config("modules.$location");
    }
}
