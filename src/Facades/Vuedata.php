<?php

namespace Itstudioat\Vuedata\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Itstudioat\Vuedata\Vuedata
 */
class Vuedata extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Itstudioat\Vuedata\Vuedata::class;
    }
}
