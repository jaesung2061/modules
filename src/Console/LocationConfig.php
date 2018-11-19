<?php

namespace Caffeinated\Modules\Console;

trait LocationConfig
{
    /**
     * @param string $key
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getLocationConfig($key = null)
    {
        $location = $this->option('location') ?: config('modules.default_location');

        if ($key) {
            return config("modules.locations.$location.$key");
        }

        return config("modules.$location");
    }
}
