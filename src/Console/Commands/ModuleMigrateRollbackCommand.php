<?php

namespace Caffeinated\Modules\Console\Commands;

use Caffeinated\Modules\Traits\MigrationTrait;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleMigrateRollbackCommand extends Command
{
    use MigrationTrait, ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:migrate:rollback {slug?} {--location=} {--database=} {--force} {--pretend} {--step=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback the last database migrations for a specific or all modules';

    /**
     * The migrator instance.
     *
     * @var \Illuminate\Database\Migrations\Migrator
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
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->migrator->setConnection($this->option('database'));

        $paths = $this->getMigrationPaths();
        $this->migrator->setOutput($this->output)->rollback(
            $paths, ['pretend' => $this->option('pretend'), 'step' => (int) $this->option('step')]
        );
    }

    /**
     * Get all of the migration paths.
     *
     * @return array
     */
    protected function getMigrationPaths()
    {
        $slug = $this->argument('slug');
        $paths = [];

        if ($slug) {
            $paths[] = module_path($slug, 'Database/Migrations', $this->option('location'));
        } else {
            foreach (modules($this->option('location'))->all() as $module) {
                $paths[] = module_path($module['slug'], 'Database/Migrations', $this->option('location'));
            }
        }

        return $paths;
    }
}
