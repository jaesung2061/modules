<?php

namespace Caffeinated\Modules\Console\Commands;

use Caffeinated\Modules\Console\BaseModuleCommand;
use Symfony\Component\Console\Input\InputArgument;

class ModuleEnableCommand extends BaseModuleCommand
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

            event("$slug.module.enabled", [$module, null]);

            $this->info('Module was enabled successfully.');
        } else {
            $this->comment('Module is already enabled.');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['slug', InputArgument::REQUIRED, 'Module slug.'],
        ];
    }
}
