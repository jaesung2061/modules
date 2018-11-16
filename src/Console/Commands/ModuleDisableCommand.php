<?php

namespace Caffeinated\Modules\Console\Commands;

use Caffeinated\Modules\Console\BaseModuleCommand;
use Symfony\Component\Console\Input\InputArgument;

class ModuleDisableCommand extends BaseModuleCommand
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

            event($slug.'.module.disabled', [$module, null]);

            $this->info('Module was disabled successfully.');
        } else {
            $this->comment('Module is already disabled.');
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
