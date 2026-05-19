<?php


namespace Packages\ShaunSocial\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Notification extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'notification';
    }
}
