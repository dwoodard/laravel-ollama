<?php

namespace Dwoodard\LaravelOllama\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dwoodard\LaravelOllama\Skeleton\SkeletonClass
 */
class LaravelOllamaFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Ollama';
    }
}
