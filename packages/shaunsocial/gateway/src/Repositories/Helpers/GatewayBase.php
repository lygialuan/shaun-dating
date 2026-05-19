<?php


namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\Gateway\Models\Gateway;

abstract class GatewayBase
{
    protected $currencyList = [];
    protected $key = '';
    protected $log = null;

    public function checkSupportCurrency($currency)
    {
        return in_array($currency, $this->currencyList);
    }

    abstract public function checkConfig($config);

    abstract public function createLinkPayment($order);

    abstract public function ipn();

    public function getLog()
    {
        if (! $this->log) {
            $this->log = Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/gateway/'.$this->key.'-'.date('Y').'-'.date('m').'.log'),
                'days' => 30
            ]);
        }

        return $this->log;
    }

    public function getConfig()
    {
        if ($this->key) {
            $gateway = Gateway::findByField('key', $this->key);
            return $gateway->getConfig();
        }
        return [];
    }


    public function canCancel($params)
    {
        return false;
    }

    public function canResume($params)
    {
        return false;
    }

    public function doResume($params)
    {
        return false;
    }

    public function doCancel($params)
    {
        return false;
    }

    public function getMaxPrice()
    {
        return 0;
    }
}
