<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;

class ModuleOptimizeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:optimize {--location=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the module cache for better performance';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Generating optimized module cache');
        if ($this->option('location')) {
            $repository = modules($this->option('location'));

            $repository->optimize();

            event('modules.optimized', [$repository->all()]);
        } else {
            foreach(modules()->repositories() as $repository) {
                $repository->optimize();

                event('modules.optimized', [$repository]);
            }
        }
    }
}
