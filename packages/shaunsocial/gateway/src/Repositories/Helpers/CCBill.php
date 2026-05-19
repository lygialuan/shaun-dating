<?php

namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Exception;

class CCBill extends GatewayBase
{
    protected $key = 'ccbill';
    protected $currencyList = ['840']; // 840 = USD (default)

    public function getClient($config = null)
    {
        if (! $this->client) {
            if (! $config) {
                $config = $this->getConfig();
            }

            if (empty($config) || empty($config['client_account_number']) || empty($config['sub_account_number'])) {
                throw new Exception('Missing CCBill credentials.');
            }

            $this->client = $config; // No SDK or Bearer token needed
        }

        return $this->client;
    }

    public function checkConfig($config)
    {
        $required = ['client_account_number', 'sub_account_number', 'flex_form_id', 'salt', 'currency'];

        foreach ($required as $key) {
            if (empty($config[$key])) {
                return [
                    'status' => false,
                    'message' => __('Missing required field: ') . $key
                ];
            }
        }

        return ['status' => true];
    }

    public function createLinkPayment($order)
    {
        $config = $this->getClient();

        $amount = round($order->getTotalAmount(), 2);
        $currency = $config['currency'] ?? '840';
        $flexFormId = $config['flex_form_id'];
        $clientAcc = $config['client_account_number'];
        $subAcc = $config['sub_account_number'];
        $salt = $config['salt'];

        $billingPeriod = 30;
        $formDigest = md5($amount . $currency . $billingPeriod . $salt);

        $query = http_build_query([
            'clientAccnum' => $clientAcc,
            'clientSubacc' => $subAcc,
            'formName' => $flexFormId,
            'currencyCode' => $currency,
            'initialPrice' => $amount,
            'initialPeriod' => $billingPeriod,
            'formDigest' => $formDigest,
            'externalReference' => $order->id,
            'customerEmail' => $order->getUser()->email,
        ]);

        $url = "https://api.ccbill.com/ccbill-api/flexforms/" . $flexFormId . "/" . "start?$query";

        return [
            'status' => true,
            'url' => $url
        ];
    }

    public function ipn()
    {
        $data = $_POST; // CCBill sends IPN via POST

        $this->getLog()->info('CCBill IPN received', $data);

        if (isset($data['externalReference']) && $data['subscription_id']) {
            $orderId = $data['externalReference'];
            $item = findByTypeId('order', $orderId);

            if ($item) {
                $item->onSuccessful($data, $data['subscription_id']);
            }
        }

        return response('IPN received', 200);
    }

    public function getMaxPrice()
    {
        return 100000; // Example max amount, can adjust
    }
}
