<?php

namespace Caffeinated\Modules\Console\Commands;

use Caffeinated\Modules\Console\BaseModuleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleSeedCommand extends BaseModuleCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:seed {slug?} {--location=} {--class=} {--database=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with records for a specific or all modules';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        $repository = modules($this->option('location'));

        if (isset($slug)) {
            if (! $repository->exists($slug)) {
                return $this->error('Module does not exist.');
            }

            if ($repository->isEnabled($slug)) {
                $this->seed($slug);
            } elseif ($this->option('force')) {
                $this->seed($slug);
            }

            return;
        } else {
            if ($this->option('force')) {
                $modules = $repository->all();
            } else {
                $modules = $repository->enabled();
            }

            foreach ($modules as $module) {
                $this->seed($module['slug']);
            }
        }
    }

    /**
     * Seed the specific module.
     *
     * @param string $module
     *
     * @return array
     */
    protected function seed($slug)
    {
        $repository = modules($this->option('location'));
        $module = $repository->where('slug', $slug);
        $params = [];
        $namespacePath = $repository->getNamespace();
        $rootSeeder = $module['basename'].'DatabaseSeeder';
        $fullPath = $namespacePath.'\\'.$module['basename'].'\Database\Seeds\\'.$rootSeeder;

        if (class_exists($fullPath)) {
            if ($this->option('class')) {
                $params['--class'] = $this->option('class');
            } else {
                $params['--class'] = $fullPath;
            }

            if ($option = $this->option('database')) {
                $params['--database'] = $option;
            }

            if ($option = $this->option('force')) {
                $params['--force'] = $option;
            }

            $this->call('db:seed', $params);

            event("$slug.module.seeded", [$module, $this->option()]);
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [['slug', InputArgument::OPTIONAL, 'Module slug.']];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the module\'s root seeder.'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run while in production.'],
        ];
    }
}
