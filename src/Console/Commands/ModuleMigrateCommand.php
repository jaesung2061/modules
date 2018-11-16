<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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

        if (!empty($this->argument('slug'))) {
            $module = modules($location)->where('slug', $this->argument('slug'));

            if (modules($location)->isEnabled($module['slug'])) {
                return $this->migrate($module['slug']);
            } elseif ($this->option('force')) {
                return $this->migrate($module['slug']);
            } else {
                return $this->error('Nothing to migrate.');
            }
        } else {
            if ($this->option('force')) {
                $modules = modules($location)->all();
            } else {
                $modules = modules($location)->enabled();
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

            event($slug.'.module.migrated', [$module, $this->option()]);

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
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run while in production.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
            ['step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually.'],
        ];
    }
}
