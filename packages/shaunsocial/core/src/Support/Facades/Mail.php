<?php


namespace Packages\ShaunSocial\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Mail extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mail';
    }
}
