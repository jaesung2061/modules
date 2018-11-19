<?php

namespace Caffeinated\Modules\Console\Commands;

use Caffeinated\Modules\Console\BaseModuleCommand;
use Caffeinated\Modules\ModuleRepositoriesFactory;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleMigrateResetCommand extends BaseModuleCommand
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:migrate:reset {slug?} {--location=} {--database=} {--force} {--pretend} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback all database migrations for a specific or all modules';

    /**
     * @var ModuleRepositoriesFactory
     */
    protected $module;

    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param ModuleRepositoriesFactory    $module
     * @param Filesystem $files
     * @param Migrator   $migrator
     */
    public function __construct(ModuleRepositoriesFactory $module, Filesystem $files, Migrator $migrator)
    {
        parent::__construct();

        $this->module = $module;
        $this->files = $files;
        $this->migrator = $migrator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->reset();
    }

    /**
     * Run the migration reset for the current list of slugs.
     *
     * Migrations should be reset in the reverse order that they were
     * migrated up as. This ensures the database is properly reversed
     * without conflict.
     *
     * @return mixed
     */
    protected function reset()
    {
        $this->migrator->setconnection($this->input->getOption('database'));

        $files = $this->migrator->setOutput($this->output)->getMigrationFiles($this->getMigrationPaths());

        $migrations = array_reverse($this->migrator->getRepository()->getRan());

        if (count($migrations) == 0) {
            $this->output->writeln("Nothing to rollback.");
        } else {
            $this->migrator->requireFiles($files);

            foreach ($migrations as $migration) {
                if (! array_key_exists($migration, $files)) {
                    continue;
                }

                $this->runDown($files[$migration], (object) ["migration" => $migration]);
            }
        }
    }

    /**
     * Run "down" a migration instance.
     *
     * @param $file
     * @param object $migration
     */
    protected function runDown($file, $migration)
    {
        $file     = $this->migrator->getMigrationName($file);
        $instance = $this->migrator->resolve($file);

        $instance->down();

        $this->migrator->getRepository()->delete($migration);

        $this->info("Rolledback: ".$file);
    }

    /**
     * Generate a list of all migration paths, given the arguments/operations supplied.
     *
     * @return array
     */
    protected function getMigrationPaths() {
        $migrationPaths = [];

        foreach ($this->getSlugsToReset() as $slug) {
            $migrationPaths[] = $this->getMigrationPath($slug);

            event("$slug.module.reset", [$this->module, $this->option()]);
        }

        return $migrationPaths;
    }

    /**
     * Using the arguments, generate a list of slugs to reset the migrations for.
     *
     * @return array
     */
    protected function getSlugsToReset()
    {
        if ($this->validSlugProvided()) {
            return [$this->argument('slug')];
        }

        if ($this->option("force")) {
            return modules($this->option('location'))->all()->pluck('slug');
        }

        return modules($this->option('location'))->enabled()->pluck('slug');
    }

    /**
     * Determine if a valid slug has been provided as an argument.
     *
     * We will accept a slug as long as it is not empty and is enalbed (or force is passed).
     *
     * @return bool
     */
    protected function validSlugProvided()
    {
        if (empty($this->argument('slug'))) {
            return false;
        }

        if (modules($this->option('location'))->isEnabled($this->argument('slug'))) {
            return true;
        }

        if ($this->option('force')) {
            return true;
        }

        return false;
    }

    /**
     * Get the console command parameters.
     *
     * @param string $slug
     *
     * @return array
     */
    protected function getParameters($slug)
    {
        $params = [];

        $params['--path'] = $this->getMigrationPath($slug);

        if ($option = $this->option('database')) {
            $params['--database'] = $option;
        }

        if ($option = $this->option('pretend')) {
            $params['--pretend'] = $option;
        }

        if ($option = $this->option('seed')) {
            $params['--seed'] = $option;
        }

        return $params;
    }

    /**
     * Get migrations path.
     *
     * @param $slug
     * @return string
     */
    protected function getMigrationPath($slug)
    {
        return module_path($slug, 'Database/Migrations');
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
            ['pretend', null, InputOption::VALUE_OPTIONAL, 'Dump the SQL queries that would be run.'],
            ['seed', null, InputOption::VALUE_OPTIONAL, 'Indicates if the seed task should be re-run.'],
        ];
    }
}
