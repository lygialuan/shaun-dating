<?php

namespace Packages\ShaunSocial\GatewayRecurring\Repositories\Helpers;

use Exception;
use Illuminate\Support\Facades\Http;

class CCBill extends GatewayRecurringBase
{
    protected $key = 'ccbill';
    protected $client = null;
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
        $required = ['client_account_number', 'sub_account_number', 'salt'];

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

    public function createLinkPayment($data, $flexFormId)
    {
        $config        = $this->getClient();
        $clientAcc     = $config['client_account_number'];
        $subAcc        = $config['sub_account_number'];
        $flexFormId    = $flexFormId;
        $amount        = round($data->amount, 2);
        $currency      = $config['currency'] ?? '840';
        $billingPeriod = 30;
        $salt          = $config['salt'];
        $formDigest    = md5($amount . $currency . $billingPeriod . $salt);

        $query = http_build_query([
            'clientAccnum'      => $clientAcc,
            'clientSubacc'      => $subAcc,
            'formName'          => $flexFormId,
            'initialPrice'      => $amount,
            'currencyCode'      => $currency,
            'initialPeriod'     => $billingPeriod,
            'formDigest'        => $formDigest,
            'externalReference' => $data->id,
            'customerEmail'     => $data->getUser()->email,
        ]);

        return [
            'status' => true,
            'url'    => "https://api.ccbill.com/ccbill-api/flexforms/" . $flexFormId . "/" . "start?$query"
        ];
    }

    public function doCancel($params)
    {
        if (empty($params['subscriptionId'])) return;

        $config = $this->getClient();

        $response = Http::asForm()->post(
            'https://api.ccbill.com/wap-frontflex/recurring/cancelSubscription',
            [
                'clientAccnum' => $config['client_account_number'],
                'clientSubacc' => $config['sub_account_number'],
                'subscriptionId' => $params['subscriptionId'],
                'username' => $config['datalink_username'],
                'password' => $config['datalink_password'],
            ]
        );
        
        $this->getLog()->info('CCBill Cancel Response', [
            'subscriptionId' => $params['subscriptionId'],
            'response' => $response->body()
        ]);

        if ($response->successful()) {
            return [
                'status' => true,
                'message' => 'Subscription cancelled'
            ];
        }

        return [
            'status' => false,
            'message' => $response->body()
        ];
    }

    public function doResume($params)
    {
        if (empty($params['subscriptionId'])) return;

        $config = $this->getClient();

        $response = Http::asForm()->post(
            'https://api.ccbill.com/wap-frontflex/recurring/reactivateSubscription',
            [
                'clientAccnum' => $config['client_account_number'],
                'clientSubacc' => $config['sub_account_number'],
                'subscriptionId' => $params['subscriptionId'],
                'username' => $config['datalink_username'],
                'password' => $config['datalink_password'],
            ]
        );

        $this->getLog()->info('CCBill Reactivate Response', [
            'subscriptionId' => $params['subscriptionId'],
            'response' => $response->body()
        ]);

        if ($response->successful()) {
            return [
                'status' => true,
                'message' => 'Subscription reactivated'
            ];
        }

        return [
            'status' => false,
            'message' => $response->body()
        ];
    }

    public function ipn()
    {
        $data = $_POST;

        $this->getLog()->info('CCBill IPN received', $data);

        $eventType         = $data['eventType'] ?? 'NewSaleSuccess';
        $subscriptionId    = $data['subscriptionId'] ?? "CODEFROMCCBB1";
        $externalReference = $data['externalReference'] ?? 16;

        if (!$eventType || !$externalReference) return response('Invalid IPN', 400);

        $item = findByTypeId('user_subscription_orders', $externalReference);

        if (!$item) return response('Subscription not found', 404);

        switch ($eventType) {
            case 'NewSaleSuccess':
            case 'RenewalSuccess':
                $item->onSuccessful($data, $subscriptionId);
                break;
            case 'NewSaleFailure':
            case 'RenewalFailure':
            case 'Chargeback':
            case 'Refund':
                $item->onFailed($subscriptionId);
                break;
        }

        return response('OK', 200);
    }

    public function getMaxPrice()
    {
        return 100000; // Example max amount, can adjust
    }
}
