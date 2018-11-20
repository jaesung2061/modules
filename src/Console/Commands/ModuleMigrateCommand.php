<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Arr;

class ModuleMigrateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:migrate {slug?} {--location=} {--database=} {--force} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations for a specific or all modules';

    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * Create a new command instance.
     *
     * @param Migrator $migrator
     */
    public function __construct(Migrator $migrator)
    {
        parent::__construct();

        $this->migrator = $migrator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->prepareDatabase();

        $location = $this->option('location');
        $repository = modules($location);

        if (!empty($this->argument('slug'))) {
            $module = $repository->where('slug', $this->argument('slug'));

            if ($repository->isEnabled($module['slug'])) {
                return $this->migrate($module['slug']);
            } elseif ($this->option('force')) {
                return $this->migrate($module['slug']);
            } else {
                return $this->error('Nothing to migrate.');
            }
        } else {
            if ($this->option('force')) {
                $modules = $repository->all();
            } else {
                $modules = $repository->enabled();
            }

            foreach ($modules as $module) {
                $this->migrate($module['slug']);
            }
        }
    }

    /**
     * Run migrations for the specified module.
     *
     * @param string $slug
     *
     * @return mixed
     */
    protected function migrate($slug)
    {
        $location = $this->option('location');

        if (modules($location)->exists($slug)) {
            $module = modules($location)->where('slug', $slug);
            $pretend = Arr::get($this->option(), 'pretend', false);
            $step = Arr::get($this->option(), 'step', false);
            $path = $this->getMigrationPath($slug);

            $this->migrator->setOutput($this->output)->run($path, ['pretend' => $pretend, 'step' => $step]);

            event('module.migrated', [$module['slug'], $this->option('location')]);

            // Finally, if the "seed" option has been given, we will re-run the database
            // seed task to re-populate the database, which is convenient when adding
            // a migration and a seed at the same time, as it is only this command.
            if ($this->option('seed')) {
                $this->call('module:seed', ['module' => $slug, '--force' => true]);
            }
        } else {
            return $this->error('Module does not exist.');
        }
    }

    /**
     * Get migration directory path.
     *
     * @param string $slug
     *
     * @return string
     */
    protected function getMigrationPath($slug)
    {
        return module_path($slug, 'Database/Migrations');
    }

    /**
     * Prepare the migration database for running.
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->option('database'));

        if (!$this->migrator->repositoryExists()) {
            $options = ['--database' => $this->option('database')];

            $this->call('migrate:install', $options);
        }
    }
}
