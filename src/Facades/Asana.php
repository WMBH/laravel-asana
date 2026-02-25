<?php

namespace WMBH\Asana\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \WMBH\Asana\Asana
 */
class Asana extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \WMBH\Asana\Asana::class;
    }
}
