<?php

namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Exception;
use GuzzleHttp\Client;

class Binance extends GatewayBase
{
    protected $client = null;
    protected $key = 'binance';

    protected $currencyList = ['USDT', 'BUSD', 'BNB', 'BTC', 'ETH'];

    public function getClient($config = null)
    {
        if (! $config) {
            $config = $this->getConfig();
        }

        if (empty($config['api_key']) || empty($config['api_secret'])) {
            throw new Exception('Missing BinancePay credentials.');
        }

        return $config;
    }

    public function createLinkPayment($order)
    {
        $config = $this->getClient();
        $timestamp = round(microtime(true) * 1000);
        $items = $order->getItems();
        foreach ($items as $item) {
            $description = $item['name'];
            break;
        }
        if(empty($description)){
            $description = $order->getSubjectType();
        }
        $body = [
            'merchantTradeNo' => $order->id,
            'orderAmount' => (string) round($order->getTotalAmount(), 2),
            'currency' => $order->getCurrency() ?? 'USD',
            'goods' => [
                'goodsType' => '01', // 01 = Virtual Goods (hard code)
                'goodsCategory' => 'D000', // D000 = Digital Products (hard code)
                'referenceGoodsId' => $order->id,
                'goodsName' => $description .' #' . $order->id,
            ],
            'returnUrl' => $order->getReturnUrl(),
            'cancelUrl' => $order->getCancelUrl(),
        ];

        try {
            $response = $this->makeRequest('POST', '/binancepay/openapi/order/create', $body, $config, $timestamp);

            return [
                'status' => true,
                'url' => $response['data']['checkoutUrl'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function makeRequest($method, $endpoint, $body = [], $config = null, $timestamp = null)
    {
        $config = $this->getClient($config);

        $client = new Client([
            'base_uri' => 'https://bpay.binanceapi.com',
            'headers' => [
                'Content-Type' => 'application/json',
                'BinancePay-Timestamp' => $timestamp,
                'BinancePay-Certificate-SN' => $config['api_key'],
                'BinancePay-Signature' => $this->signRequest($body, $config['api_secret'], $timestamp),
            ]
        ]);

        $response = $client->request($method, $endpoint, [
            'json' => $body,
        ]);

        $result = json_decode($response->getBody(), true);

        if (!isset($result['status']) || $result['status'] !== 'SUCCESS') {
            throw new Exception($result['message'] ?? 'BinancePay error');
        }

        return $result;
    }

    protected function signRequest($body, $secret, $timestamp)
    {
        $payload = $timestamp . json_encode($body, JSON_UNESCAPED_SLASHES);
        return strtoupper(hash_hmac('sha512', $payload, $secret));
    }

    public function ipn()
    {
        $input = @file_get_contents("php://input");
        $data = json_decode($input, true);
        $this->getLog()->info('Binance IPN received');
        $this->getLog()->info(print_r($data, true));

        if ($data['eventType'] === 'PAYMENT_SUCCESS') {
            $orderId = $data['data']['merchantTradeNo'];
            $item = findByTypeId('wallet_order', $orderId);
            if ($item) {
                $item->onSuccessful($data, $data['data']['prepayId']);
            }
        }

        return response('ok', 200);
    }

    public function getMaxPrice()
    {
        return 10000000;
    }

    public function checkConfig($config): array
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $testBody = [
                'merchantTradeNo' => uniqid('test_', true),
                'orderAmount' => '1.00',
                'currency' => $config['currency'] ?? 'USDT',
                'goods' => [
                    'goodsType' => '01',
                    'goodsCategory' => 'D000',
                    'referenceGoodsId' => 'test',
                    'goodsName' => 'Test Order',
                ],
                'returnUrl' => url('/'),
                'cancelUrl' => url('/'),
            ];

            $this->makeRequest('POST', '/binancepay/openapi/order/create', $testBody, $config, $timestamp);

            return ['status' => true, 'message' => 'Credentials valid'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
