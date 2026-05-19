<?php


namespace Packages\ShaunSocial\Wallet\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Wallet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'wallet';
    }
}
