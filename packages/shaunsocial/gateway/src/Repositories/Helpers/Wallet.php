<?php


namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Exception;

class Wallet extends GatewayBase
{
    public function checkConfig($config)
    {
        return true;
    }

    public function createLinkPayment($order)
    {
        return [];
    }

    public function ipn()
    {
        
    }

    public function canCancel($params)
    {
        return true;
    }

    public function canResume($params)
    {
        return true;
    }

    public function doResume($params)
    {
        return true;
    }
}