<?php

namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Exception;

class NowPayments extends GatewayBase
{
    protected $key = 'nowpayments';
    protected $client = null;
    protected $currencyList = ['USD', 'USDT', 'BTC', 'ETH'];

    public function getClient($config = null)
    {
        if (! $this->client) {
            if (! $config) {
                $config = $this->getConfig();
            }

            if (empty($config['api_key'])) {
                throw new Exception('Missing NOWPayments API key.');
            }

            $this->client = $config['api_key'];
        }

        return $this->client;
    }

    public function checkConfig($config)
    {
        if (empty($config['api_key'])) {
            return [
                'status' => false,
                'message' => __('The NOWPayments API key is required.')
            ];
        }

        try {
            $result = $this->makeRequest('GET', '/v1/currencies', [], $config);
            if (!isset($result['currencies'])) {
                throw new Exception('Invalid NOWPayments credentials.');
            }
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
        $amount = round($order->getTotalAmount(), 2);
        $config = $this->getConfig();

        $items = $order->getItems();
        foreach ($items as $item) {
            $description = $item['name'];
            break;
        }
        if(empty($description)){
            $description = $order->getSubjectType();
        }
        $body = [
            'price_amount' => $amount,
            'price_currency' => $order->getCurrency() ?? 'USD',
            'order_id' => $order->id."|".$order->getSubjectType(),
            'order_description' => $description . ' #' . $order->id,
            'ipn_callback_url' => route('gateway.nowpayments.ipn'),
        ];
        try {
            $response = $this->makeRequest('POST', '/v1/invoice', $body);
            return [
                'status' => true,
                'url' => $response['invoice_url'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function ipn()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->getLog()->info('NOWPayments IPN', $data);

        if (isset($data['order_id']) && $data['payment_status'] === 'finished') {
            $order = explode("|",$data['order_id']); //$order[0] = id, $order[1] = type
            $item = findByTypeId($order[1], (int)$order[0]);
            if ($item) {
                $item->onSuccessful($data, $data['payment_id']);
            }
        }

        return response('ok', 200);
    }

    protected function makeRequest($method, $endpoint, $body = [], $config = null)
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.nowpayments.io',
            'headers' => [
                'x-api-key' => $config['api_key'] ?? $this->getClient(),
                'Content-Type' => 'application/json',
            ],
        ]);

        $options = ['json' => $body];
        $response = $client->request($method, $endpoint, $options);
        $result = json_decode($response->getBody(), true);

        if (isset($result['error'])) {
            throw new Exception($result['error']);
        }

        return $result;
    }

    public function getMaxPrice()
    {
        return 100000; // can be adjusted
    }
}
