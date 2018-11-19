<?php

namespace Caffeinated\Modules\Tests;

use Illuminate\Support\Collection;

class RepositoryTest extends BaseTestCase
{
    protected $finder;

    public function setUp()
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'RepositoryMod2', '--quick' => 'quick']);

        $this->artisan('make:module', ['slug' => 'RepositoryMod1', '--quick' => 'quick']);

        $this->artisan('make:module', ['slug' => 'RepositoryMod3', '--quick' => 'quick']);
    }

    /** @test */
    public function it_can_check_if_module_is_disabled()
    {
        $this->assertFalse(modules()->isDisabled('repositorymod1'));

        modules()->disable('repositorymod1');

        $this->assertTrue(modules()->isDisabled('repositorymod1'));
    }

    /** @test */
    public function it_can_check_if_module_is_enabled()
    {
        $this->assertTrue(modules()->isEnabled('repositorymod1'));

        modules()->disable('repositorymod1');

        $this->assertFalse(modules()->isEnabled('repositorymod1'));
    }

    /** @test */
    public function it_can_check_if_the_module_exists()
    {
        $this->assertTrue(modules()->exists('repositorymod1'));

        $this->assertFalse(modules()->exists('repositorymod4'));
    }

    /** @test */
    public function it_can_count_the_modules()
    {
        $this->assertSame(3, (int)modules()->count());
    }

    /** @test */
    public function it_can_get_a_collection_of_disabled_modules()
    {
        $this->assertSame(0, (int)modules()->disabled()->count());

        modules()->disable('repositorymod1');

        $this->assertSame(1, (int)modules()->disabled()->count());
    }

    /** @test */
    public function it_can_get_a_collection_of_enabled_modules()
    {
        $this->assertSame(3, (int)modules()->enabled()->count());

        modules()->disable('repositorymod1');

        $this->assertSame(2, (int)modules()->enabled()->count());
    }

    /** @test */
    public function it_can_get_a_module_based_on_where()
    {
        $slug = modules()->where('slug', 'repositorymod1');

        $this->assertInstanceOf(Collection::class, $slug);

        $this->assertCount(8, $slug);

        //

        $basename = modules()->where('basename', 'Repositorymod1');

        $this->assertInstanceOf(Collection::class, $basename);

        $this->assertCount(8, $basename);

        //

        $name = modules()->where('name', 'Repositorymod1');

        $this->assertInstanceOf(Collection::class, $name);

        $this->assertCount(8, $name);
    }

    /** @test */
    public function it_can_get_all_the_modules()
    {
        $this->assertCount(3, modules()->all());

        $this->assertInstanceOf(Collection::class, modules()->all());
    }

    /** @test */
    public function it_can_get_correct_module_and_manifest_for_legacy_modules()
    {
        $this->artisan('make:module', ['slug' => 'barbiz', '--quick' => 'quick']);

        // Quick and fast way to simulate legacy Module FolderStructure
        // https://github.com/caffeinated/modules/pull/224
        rename(realpath(module_path('barbiz')), realpath(module_path()) . '/BarBiz');
        file_put_contents(realpath(module_path()) . '/BarBiz/module.json', json_encode(array(
            'name' => 'BarBiz', 'slug' => 'BarBiz', 'version' => '1.0', 'description' => '',
        ), JSON_PRETTY_PRINT));

        $this->assertSame(
            '{"name":"BarBiz","slug":"BarBiz","version":"1.0","description":""}',
            json_encode(modules()->getManifest('BarBiz'))
        );

        $this->assertSame(
            realpath(module_path() . '/BarBiz'),
            realpath(modules()->getModulePath('BarBiz'))
        );
    }

    /** @test */
    public function it_can_get_correct_slug_exists_for_legacy_modules()
    {
        $this->artisan('make:module', ['slug' => 'foobar', '--quick' => 'quick']);

        // Quick and fast way to simulate legacy Module FolderStructure
        // https://github.com/caffeinated/modules/pull/279
        // https://github.com/caffeinated/modules/pull/349
        rename(realpath(module_path('foobar')), realpath(module_path()) . '/FooBar');
        file_put_contents(realpath(module_path()) . '/FooBar/module.json', json_encode(array(
            'name' => 'FooBar', 'slug' => 'FooBar', 'version' => '1.0', 'description' => '',
        ), JSON_PRETTY_PRINT));

        $this->assertTrue(modules()->exists('FooBar'));
    }

    /** @test */
    public function it_can_get_custom_modules_namespace()
    {
        $this->app['config']->set('modules.namespace', 'App\\Foo\\Bar\\Baz\\Tests');

        $this->assertSame('App\Foo\Bar\Baz\Tests', modules()->getNamespace());

        $this->app['config']->set('modules.namespace', 'App\\Foo\\Baz\\Bar\\Tests\\');

        $this->assertSame('App\Foo\Baz\Bar\Tests', modules()->getNamespace());
    }

    /** @test */
    public function it_can_get_default_modules_namespace()
    {
        $this->assertSame('App\Modules', modules()->getNamespace());
    }

    /** @test */
    public function it_can_get_default_modules_path()
    {
        $this->assertSame(base_path() . '/modules', modules()->getPath());
    }

    /** @test */
    public function it_can_get_manifest_of_module()
    {
        $manifest = modules()->getManifest('repositorymod1');

        $this->assertSame(
            '{"name":"Repositorymod1","slug":"repositorymod1","version":"1.0","description":"This is the description for the Repositorymod1 module."}',
            $manifest->toJson()
        );
    }

    /** @test */
    public function it_can_get_module_path_of_module()
    {
        $path = modules()->getModulePath('repositorymod1');

        $this->assertSame(
            base_path() . '/modules/Repositorymod1/',
            $path
        );
    }

    /** @test */
    public function it_can_get_property_of_module()
    {
        $this->assertSame('Repositorymod1', modules()->get('repositorymod1::name'));

        $this->assertSame('1.0', modules()->get('repositorymod2::version'));

        $this->assertSame('This is the description for the Repositorymod3 module.', modules()->get('repositorymod3::description'));
    }

    /** @test */
    public function it_can_get_the_modules_slugs()
    {
        $this->assertCount(3, modules()->slugs());

        modules()->slugs()->each(function ($key, $value) {
            $this->assertSame('repositorymod' . ($value + 1), $key);
        });
    }

    /** @test */
    public function it_can_set_custom_modules_path_in_runtime_mode()
    {
        modules()->setPath(base_path('tests/runtime/modules'));

        $this->assertSame(
            base_path() . '/tests/runtime/modules',
            modules()->getPath()
        );
    }

    /** @test */
    public function it_can_set_property_of_module()
    {
        $this->assertSame('Repositorymod1', modules()->get('repositorymod1::name'));

        modules()->set('repositorymod1::name', 'FooBarRepositorymod1');

        $this->assertSame('FooBarRepositorymod1', modules()->get('repositorymod1::name'));

        //

        $this->assertSame('1.0', modules()->get('repositorymod3::version'));

        modules()->set('repositorymod3::version', '1.3.3.7');

        $this->assertSame('1.3.3.7', modules()->get('repositorymod3::version'));
    }

    /** @test */
    public function it_can_sortby_asc_slug_the_modules()
    {
        $sortByAsc = array_keys(modules()->sortby('slug')->toArray());

        $this->assertSame($sortByAsc[0], 'Repositorymod1');
        $this->assertSame($sortByAsc[1], 'Repositorymod2');
        $this->assertSame($sortByAsc[2], 'Repositorymod3');
    }

    /** @test */
    public function it_can_sortby_desc_slug_the_modules()
    {
        $sortByAsc = array_keys(modules()->sortbyDesc('slug')->toArray());

        $this->assertSame($sortByAsc[0], 'Repositorymod3');
        $this->assertSame($sortByAsc[1], 'Repositorymod2');
        $this->assertSame($sortByAsc[2], 'Repositorymod1');
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_will_throw_exception_by_invalid_json_manifest_file()
    {
        file_put_contents(realpath(module_path()) . '/Repositorymod1/module.json', 'invalidjsonformat');

        $manifest = modules()->getManifest('repositorymod1');
    }

    /**
     * @test
     * @expectedException \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function it_will_throw_file_not_found_exception_by_unknown_module()
    {
        $manifest = modules()->getManifest('unknown');
    }
}