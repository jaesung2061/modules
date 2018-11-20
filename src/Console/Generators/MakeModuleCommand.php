<?php

namespace Caffeinated\Modules\Console\Generators;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module
        {slug : The slug of the module}
        {--Q|quick : Skip the make:module wizard and use default values}
    	{--location= : The modules location}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Caffeinated module and bootstrap it';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Array to store the configuration details.
     *
     * @var array
     */
    protected $container;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['quick', null, InputOption::VALUE_OPTIONAL, 'Skip the make:module wizard and use default values.', null],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->container['slug'] = str_slug($this->argument('slug'));
        $this->container['name'] = studly_case($this->container['slug']);
        $this->container['version'] = '1.0';
        $this->container['description'] = 'This is the description for the ' . $this->container['name'] . ' module.';

        if ($this->option('quick')) {
            $location = $this->option('location');
            $this->container['basename'] = studly_case($this->container['slug']);
            $this->container['namespace'] = config("modules.locations.$location.namespace") . $this->container['basename'];
            return $this->generate();
        }

        $this->displayHeader('make_module_introduction');

        $this->stepOne();
    }

    /**
     * Generate the module.
     */
    protected function generate()
    {
        $this->generateModule();

        event('module.made', [$this->container['slug'], $this->option('location')]);

        $this->info("\nModule generated successfully.");
    }

    /**
     * Pull the given stub file contents and display them on screen.
     *
     * @param string $file
     * @param string $level
     *
     * @return mixed
     */
    protected function displayHeader($file = '', $level = 'info')
    {
        $stub = $this->files->get(__DIR__ . '/../../../resources/stubs/console/' . $file . '.stub');

        return $this->$level($stub);
    }

    /**
     * Step 1: Configure module manifest.
     *
     * @return mixed
     */
    protected function stepOne()
    {
        $location = $this->option('location');
        $this->displayHeader('make_module_step_1');

        $this->container['name'] = $this->ask('Please enter the name of the module:', $this->container['name']);
        $this->container['slug'] = $this->ask('Please enter the slug for the module:', $this->container['slug']);
        $this->container['version'] = $this->ask('Please enter the module version:', $this->container['version']);
        $this->container['description'] = $this->ask('Please enter the description of the module:', $this->container['description']);
        $this->container['basename'] = studly_case($this->container['slug']);
        $this->container['namespace'] = config("modules.locations.$location.namespace") . $this->container['basename'];

        $this->comment('You have provided the following manifest information:');
        $this->comment('Name:                       ' . $this->container['name']);
        $this->comment('Slug:                       ' . $this->container['slug']);
        $this->comment('Version:                    ' . $this->container['version']);
        $this->comment('Description:                ' . $this->container['description']);
        $this->comment('Basename (auto-generated):  ' . $this->container['basename']);
        $this->comment('Namespace (auto-generated): ' . $this->container['namespace']);

        if ($this->confirm('If the provided information is correct, type "yes" to generate.')) {
            $this->comment('Thanks! That\'s all we need.');
            $this->comment('Now relax while your module is generated.');

            $this->generate();
        } else {
            return $this->stepOne();
        }

        return true;
    }

    /**
     * Generate defined module folders.
     */
    protected function generateModule()
    {
        $modulesDirectory = module_path(null, '', $this->option('location'));

        if (!$this->files->isDirectory($modulesDirectory)) {
            $this->files->makeDirectory($modulesDirectory);
        }

        $pathMap = config('modules.pathMap');
        $directory = module_path(null, $this->container['basename'], $this->option('location'));
        $source = __DIR__ . '/../../../resources/stubs/module';

//        if ($this->files->isDirectory($directory)) {
//            throw new Exception("Directory already exists at [$directory]");
//        }

        // TODO REMOVE
        if (app()->environment('testing')) {
            $this->files->deleteDirectory($directory);
        }

        $this->files->makeDirectory($directory);

        $sourceFiles = $this->files->allFiles($source, true);

        if (!empty($pathMap)) {
            $search = array_keys($pathMap);
            $replace = array_values($pathMap);
        }

        foreach ($sourceFiles as $file) {
            $contents = $this->replacePlaceholders($file->getContents());
            $subPath = $file->getRelativePathname();

            if (!empty($pathMap)) {
                $subPath = str_replace($search, $replace, $subPath);
            }

            $filePath = $directory . '/' . $subPath;
            $dir = dirname($filePath);

            if (!$this->files->isDirectory($dir)) {
                $this->files->makeDirectory($dir, 0755, true);
            }

            $this->files->put($filePath, $contents);
        }
    }

    protected function replacePlaceholders($contents)
    {
        $find = [
            'DummyBasename',
            'DummyNamespace',
            'DummyName',
            'DummySlug',
            'DummyVersion',
            'DummyDescription',
        ];

        $replace = [
            $this->container['basename'],
            $this->container['namespace'],
            $this->container['name'],
            $this->container['slug'],
            $this->container['version'],
            $this->container['description'],
        ];

        return str_replace($find, $replace, $contents);
    }
}
