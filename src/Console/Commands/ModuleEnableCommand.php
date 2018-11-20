<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;

class ModuleEnableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:enable {slug?} {--location=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable a module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        $modules = modules($this->option('location'));

        if ($modules->isDisabled($slug)) {
            $modules->enable($slug);

            $module = $modules->where('slug', $slug);

            event('module.enabled', [$module['slug'], $this->option('location')]);

            $this->info('Module was enabled successfully.');
        } else {
            $this->comment('Module is already enabled.');
        }
    }
}
