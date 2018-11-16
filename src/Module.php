<?php

namespace Caffeinated\Modules;

class Module
{
    /**
     * @var array
     */
    protected $manifest;

    /**
     * Module constructor.
     *
     * @param array $manifest
     */
    public function __construct(array $manifest)
    {
        $this->manifest = $manifest;
    }

    public function path()
    {
        //
    }

    public function slug()
    {
        //
    }

    public function tag()
    {

    }
}