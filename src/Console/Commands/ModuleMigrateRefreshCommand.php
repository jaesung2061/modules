<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleMigrateRefreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:migrate:refresh {slug?} {--database=} {--location=} {--pretend} {--force} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset and re-run all migrations for a specific or all modules';

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

        $slug = $this->argument('slug');

        $this->call('module:migrate:reset', [
            'slug'       => $slug,
            '--database' => $this->option('database'),
            '--force'    => $this->option('force'),
            '--pretend'  => $this->option('pretend'),
            '--location'  => $this->option('location'),
        ]);

        $this->call('module:migrate', [
            'slug'       => $slug,
            '--database' => $this->option('database'),
            '--location' => $this->option('location'),
        ]);

        if ($this->needsSeeding()) {
            $this->runSeeder($slug, $this->option('database'), $this->option('location'));
        }

        if (isset($slug)) {
            $module = modules($this->option('location'))->where('slug', $slug);

            event('module.refreshed', [$module['slug'], $this->option('location')]);

            $this->info('Module has been refreshed.');
        } else {
            $this->info('All modules have been refreshed.');
        }
    }

    /**
     * Determine if the developer has requested database seeding.
     *
     * @return bool
     */
    protected function needsSeeding()
    {
        return $this->option('seed');
    }

    /**
     * Run the module seeder command.
     *
     * @param null $slug
     * @param string $database
     * @param null $location
     */
    protected function runSeeder($slug = null, $database = null, $location = null)
    {
        $this->call('module:seed', [
            'slug'       => $slug,
            '--database' => $database,
            '--location' => $location,
        ]);
    }
}
