<?php


namespace Packages\ShaunSocial\GatewayRecurring\Repositories\Helpers;

use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\GatewayRecurring\Models\GatewayRecurring;

abstract class GatewayRecurringBase
{
    protected $currencyList = [];
    protected $key = '';
    protected $log = null;

    public function checkSupportCurrency($currency)
    {
        return in_array($currency, $this->currencyList);
    }

    abstract public function checkConfig($config);

    abstract public function createLinkPayment($order, $flexFormId);

    abstract public function ipn();

    abstract public function doCancel($params);

    abstract public function doResume($params);

    public function getLog()
    {
        if (! $this->log) {
            $this->log = Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/gateway_recurring/'.$this->key.'-'.date('Y').'-'.date('m').'.log'),
                'days' => 30
            ]);
        }

        return $this->log;
    }

    public function getConfig()
    {
        if ($this->key) {
            $gateway = GatewayRecurring::findByField('key', $this->key);
            return $gateway->getConfig();
        }
        return [];
    }

    public function canCancel($params)
    {
        return true;
    }

    public function canResume($params)
    {
        return true;
    }

    public function getMaxPrice()
    {
        return 0;
    }
}
