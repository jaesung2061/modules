<?php

return [

    'default_location' => 'modules',

    'locations' => [
        'modules' => [
            'driver' => 'local',
            'path' => app_path('Modules'),
            'namespace' => 'Modules\\',
            'enabled_by_default' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules Default Service Provider class name
    |--------------------------------------------------------------------------
    |
    | Define class name to use as default module service provider for locations
    | if that location does not have a custom provider class.
    |
    */

    'default_provider_class' => 'Providers\\ModuleServiceProvider',

    /*
    |--------------------------------------------------------------------------
    | Default Module Driver
    |--------------------------------------------------------------------------
    |
    | By default, the local storage driver will be used. If you wish to use
    | a custom driver, create a class and define it in the 'drivers' section
    | below.
    |
    */

    'default_driver' => 'local',

    /*
     |--------------------------------------------------------------------------
     | Custom Module Drivers
     |--------------------------------------------------------------------------
     |
     | Using custom module drivers, the 'driver' value need to be set to 'custom'
     | The path to the driver need to be set in addition at custom_driver.
     |
     | @warn: This value will be only considered if driver is set to custom.
     |
     */

    'drivers' => [
        'local' => 'Caffeinated\Modules\Repositories\LocalRepository',
        //'mysql' => 'Custom\MysqlRepository',
    ],

    /*
    |--------------------------------------------------------------------------
    | Remap Module Subdirectories
    |--------------------------------------------------------------------------
    |
    | Redefine how module directories are structured. The mapping here will
    | be respected by all commands and generators.
    |
    */

    'pathMap' => [
        // To change where migrations go, specify the default
        // location as the key and the new location as the value:
        // 'Database/Migrations' => 'src/Database/Migrations',
    ],
];