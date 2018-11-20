<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;

class ModuleDisableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:disable {slug} {--location=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable a module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        $modules = modules($this->option('location'));

        if ($modules->isEnabled($slug)) {
            $modules->disable($slug);

            $module = $modules->where('slug', $slug);

            event('module.disabled', [$module['slug'], $this->option('location')]);

            $this->info('Module was disabled successfully.');
        } else {
            $this->comment('Module is already disabled.');
        }
    }
}
