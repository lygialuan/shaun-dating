<?php


namespace Packages\ShaunSocial\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Theme extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'theme';
    }
}
