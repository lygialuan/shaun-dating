<?php


namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Exception;

class Stripe extends GatewayBase
{
    protected $currencyList = ['AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'TWD', 'THB', 'TRY', 'USD','INR'];
    protected $client = null;
    protected $key = 'stripe';

    public function getClient($config = null)
    {
        if (! $this->client) {
            if (! $config) {                
                $config = $this->getConfig();
            }
            $this->client = new \Stripe\StripeClient($config['secret_key']);
        }

        return $this->client;
    }

    public function checkConfig($config)
    {
        if (empty($config['secret_key'])){
            return [
                'status' => false,
                'message' => __('The api secret key is required.')
            ];
        }

        if (empty($config['publishable_key'])){
            return [
                'status' => false,
                'message' => __('The api publishable key is required.')
            ];
        }

        if (empty($config['signing_secret'])){
            return [
                'status' => false,
                'message' => __('The webhook signing secret is required.')
            ];
        }

        $client = $this->getClient($config);

        try {
            $client->plans->all(['limit' => 3]);
        } catch (Exception $e)
        {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
        
        return ['status' => true];
    }

    public function createLinkPayment($order)
    {
        $client = $this->getClient();
        $items = $order->getItems();
        $orderData = [
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'success_url' => $order->getReturnUrl(),
            'cancel_url' => $order->getCancelUrl(),
            'payment_intent_data' => array('metadata'=>array('type'=> $order->getSubjectType(),'id'=> $order->id))
        ];
        foreach ($items as $item) {
            $orderData['line_items'][] = [
                'price_data' => [
                    'currency' => $order->getCurrency(),
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => round($item['amount'],2) * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }

        try {
            $result = $client->checkout->sessions->create($orderData);

            return [
                'status' => true,
                'url' => $result->url
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
        $input = @file_get_contents("php://input");
        $data = json_decode($input);
        $this->getLog()->info('Start ipn');
        $this->getLog()->info(print_r($data, true));
        $config = $this->getConfig();
        if (! $config) {
            return response('ok', 200);
        }

        $sigHeader = $_SERVER["HTTP_STRIPE_SIGNATURE"];
        $event = null;
        
        try {
        	$event = \Stripe\Webhook::constructEvent($input, $sigHeader, $config['signing_secret']);
        } catch(Exception $e) {
            $this->getLog()->info('Error when check ipn: '.$e->getMessage());
        	return response('ok', 200);
        }

        switch ($data->type) {
            case "charge.succeeded" :
                if (isset($data->data->object->metadata->type) && isset($data->data->object->metadata->id))
				{
					$item = findByTypeId($data->data->object->metadata->type,$data->data->object->metadata->id);
                    if ($item) {
                        $item->onSuccessful(json_decode(json_encode($data), true), $data->data->object->id);
                    }                    
				}

                break;
        }

        return response('ok', 200);
    }

    public function getMaxPrice()
    {
        return 5000;
    }
}