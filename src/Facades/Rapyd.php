<?php

namespace Sabaab\Rapyd\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sabaab\Rapyd\Rapyd
 */
class Rapyd extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Sabaab\Rapyd\Rapyd::class;
    }
}
