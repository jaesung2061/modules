<?php

namespace Caffeinated\Modules\Console\Generators;

use Caffeinated\Modules\Console\BaseModuleCommand;

class MakeMigrationCommand extends BaseModuleCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module:migration
    	{slug : The slug of the module.}
    	{name : The name of the migration.}
    	{--create= : The table to be created.}
        {--table= : The table to migrate.}
    	{--location= : The modules location}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module migration file';

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['create', null, InputOption::VALUE_OPTIONAL, 'The table to be created.', null],
            ['table', null, InputOption::VALUE_OPTIONAL, 'The table to migrate.', null],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $arguments = $this->argument();
        $option = $this->option();
        $options = [];

        array_walk($option, function (&$value, $key) use (&$options) {
            $options['--' . $key] = $value;
        });

        unset($arguments['slug']);
        unset($options['--location']);

        $modulePath = module_path($this->argument('slug'), 'Database/Migrations', $this->option('location'));
        $options['--path'] = str_replace(realpath(base_path()), '', realpath($modulePath));
        $options['--path'] = ltrim($options['--path'], '/');

        return $this->call('make:migration', array_merge($arguments, $options));
    }
}
