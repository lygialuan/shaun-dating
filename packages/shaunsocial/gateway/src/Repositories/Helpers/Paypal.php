<?php
namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Exception;

class Paypal extends GatewayBase
{
    protected $currencyList = ['AUD','BRL','CAD','CNY','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','GBP','SGD','SEK','CHF','THB','USD'];
    protected $client = null;
    protected $key = 'paypal';

    protected function getClient($config = null)
    {    
        if (! $this->client) {
            if (! $config) {
                $config = $this->getConfig();
            }
            $mode = 'live';
            if (!empty($config['sandbox'])) {
                $mode = 'sandbox';
            }
            $config = [
                'mode'    =>  $mode,
                'live' => [
                    'client_id'         => $config['client_id'],
                    'client_secret'     => $config['secret'],
                ],
                'sandbox' => [
                    'client_id'         => $config['client_id'],
                    'client_secret'     => $config['secret'],
                ],
                'payment_action' => 'Sale',
                'currency'       => 'USD',
                'notify_url'     => route('gateway.paypal.ipn'),
                'locale'         => 'en_US',
                'validate_ssl'   => true,
            ];
            $provider = new PayPalClient($config);
    
            $this->client = $provider;
        }

        return $this->client;
    }

    public function checkConfig($config)
    {
        if (empty($config['client_id'])){
            return [
                'status' => false,
                'message' => __('The paypal client id is required.')
            ];
        }

        if (empty($config['secret'])){
            return [
                'status' => false,
                'message' => __('The paypal secret is required.')
            ];
        }

        if (empty($config['webhook'])){
            return [
                'status' => false,
                'message' => __('The webhook id is required.')
            ];
        }

        $client = $this->getClient($config);
        try {
            $token = $client->getAccessToken();
            $client->setAccessToken($token);
            $order = $client->createOrder([
                'intent'=> 'CAPTURE',
                'purchase_units'=> [
                     [
                        'amount'=> [
                            'currency_code'=> 'USD',
                            'value'=> 1
                        ],
                         'description' => 'test'
                    ]
                ],
            ]);
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
        
        return ['status' => true];
    }

    public function createLinkPayment($order)
    {
        $referenceId = $order->getSubjectType().'|'.$order->id;
        $client = $this->getClient();
        $items = $order->getItems();
        $orderData = [
            'intent'=> 'CAPTURE',
            'purchase_units'=> [
                [  
                    'reference_id' => $referenceId,
                    'amount'=> [
                        'currency_code'=> $order->getCurrency(),
                        'value'=> $order->getTotalAmount(),
                        'breakdown' => [
                            'item_total' => [
                                'value' => $order->getTotalAmount(),
                                'currency_code' => $order->getCurrency(),
                            ],
                        ]
                    ],
                ]
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'return_url' => $order->getReturnUrl(),
                        'cancel_url' => $order->getCancelUrl()
                    ]
                ]
            ]
        ];
        foreach ($items as $item) {
            $orderData['purchase_units'][0]['items'][] = [
                'name' => $item['name'],
                'unit_amount' => [
                    'value' => $item['amount'],
                    'currency_code' => $order->getCurrency(),
                ],
                'quantity' => $item['quantity']
            ];
        }

        $this->getLog()->info(print_r($orderData, true));
        try {
            $token = $client->getAccessToken();
            $client->setAccessToken($token);
            $orderRespone = $client->createOrder($orderData);
            $this->getLog()->info(print_r($orderRespone, true));
            if (isset($orderRespone['error'])) {
                return [
                    'status' => false,
                    'url' => $orderRespone['error']['message']
                ];
            }
            return [
                'status' => true,
                'url' => $orderRespone['links'][1]['href']
            ];
        }
        catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function ipn()
    {
        $data = file_get_contents('php://input');
		$this->getLog()->info('start ipn');
		$this->getLog()->info($data);
		
		$client = $this->getClient();
		$token = $client->getAccessToken();
        $client->setAccessToken($token);
        $config = $this->getConfig();
		$client->setWebHookID($config['webhook']);
		$respone = $client->verifyIPN(request());
		$this->getLog()->info(print_r($respone, true));
        if (!empty($respone['verification_status']) && $respone['verification_status'] == 'SUCCESS') {
            $data = json_decode($data, true);
            switch ($data['event_type']) {
                case 'CHECKOUT.ORDER.APPROVED':
                    if (!empty($data['resource']['purchase_units'][0]['reference_id'])) {
                        $referenceId = $data['resource']['purchase_units'][0]['reference_id'];
                        $tmp = explode('|', $referenceId);
                        if (count($tmp) == 2) {
                            $item = findByTypeId($tmp[0],$tmp[1]);
                            if ($item) {
                                $item->onSuccessful($data, $data['resource']['id']);
                            }
                        }
                    }
                    break;
            }
        } else {

        }
    }

    public function getMaxPrice()
    {
        return 5000;
    }
}