<?php


namespace Packages\ShaunSocial\Gift\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Gift extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'gift';
    }
}
