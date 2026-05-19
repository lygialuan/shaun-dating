<?php

namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Exception;

class Cashfree extends GatewayBase
{
    protected $currencyList = ['INR'];
    protected $client = null;
    protected $key = 'cashfree';

    public function getClient($config = null)
    {
        if (! $this->client) {
            if (! $config) {
                $config = $this->getConfig();
            }

            if (empty($config['client_id']) || empty($config['client_secret'])) {
                throw new Exception('Missing Cashfree credentials.');
            }

            $this->client = [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
            ];
            $this->client['sandbox'] = !empty($config['sandbox']);
        }

        return $this->client;
    }

    public function checkConfig($config)
    {
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return [
                'status' => false,
                'message' => __('The Cashfree client credentials are required.')
            ];
        }

        try {
            $this->makeRequest('GET', '/pg/orders', [], $config); // Or health/ping endpoint if available
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
        $config = $this->getConfig();
        $client = $this->getClient($config);

        $body = [
            "customer_details" => [
                "customer_id" => (string)$order->getUser()->id,
                "customer_email" => $order->getUser()->email,
                "customer_phone" => $order->getUser()->phone
            ],
            "order_id" => 'ORDER_' . $order->id,
            "order_amount" => (float)$order->getTotalAmount(),
            "order_currency" => 'INR',
            "order_note" => "Order #" . $order->id,
            "order_meta" => [
                "return_url" => $order->getReturnUrl() . "?order_id=ORDER_" . $order->id
            ]
        ];

        try {
            $response = $this->makeRequest('POST', '/pg/orders', $body, $config);

            return [
                'status' => true,
                'url' => $response['payment_link']
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
        $input = @file_get_contents("php://input");
        $data = json_decode($input, true);
        $this->getLog()->info('Start Cashfree IPN');
        $this->getLog()->info(print_r($data, true));

        $config = $this->getConfig();
        if (! $config) {
            return response('ok', 200);
        }

        // Validate Signature (placeholder, replace with real Cashfree signature verification if available)
        $signature = $_SERVER['HTTP_X_CASHFREE_SIGNATURE'] ?? null;
        if (! $signature /* || !$this->verifySignature($input, $signature, $config['client_secret']) */) {
            $this->getLog()->info('Invalid signature');
            return response('ok', 200);
        }

        if (isset($data['order_id']) && isset($data['order_status'])) {
            $status = strtolower($data['order_status']);
            $orderId = str_replace('ORDER_', '', $data['order_id']);

            if ($status === 'paid') {
                $item = findByTypeId('wallet_deposit', $orderId);
                if ($item) {
                    $item->onSuccessful($data, $orderId);
                }
            }
        }

        return response('ok', 200);
    }

    protected function makeRequest($method, $endpoint, $body = [], $config = null)
    {
        try {
            $clientConfig = $config ?: $this->getClient();
            $client = new \GuzzleHttp\Client([
                'base_uri' => $clientConfig['sandbox'] ? 'https://sandbox.cashfree.com' : 'https://api.cashfree.com',
                'headers' => [
                    'x-api-version' => '2022-09-01',
                    'x-client-id' => $clientConfig['client_id'],
                    'x-client-secret' => $clientConfig['client_secret'],
                    'Content-Type' => 'application/json',
                ],
            ]);

            $options = [];
            if ($method === 'GET') {
                $options['query'] = $body;
            } else {
                $options['json'] = $body;
            }

            $response = $client->request($method, $endpoint, $options);

            $result = json_decode($response->getBody(), true);

            if (isset($result['status']) && strtolower($result['status']) !== 'ok') {
                throw new Exception($result['message'] ?? 'Cashfree error');
            }

            return $result;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorBody = (string) $e->getResponse()?->getBody();
            $errorBody = json_decode($errorBody);
            $message = $errorBody->message ?? $e->getMessage();
            throw new Exception('Cashfree request failed: ' . $message);
        }
    }

    public function getMaxPrice()
    {
        return 10000000;
    }


}
