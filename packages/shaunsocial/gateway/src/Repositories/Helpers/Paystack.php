<?php

namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Exception;

class Paystack extends GatewayBase
{
    protected $currencyList = ['NGN', 'GHS', 'ZAR', 'KES', 'USD'];
    protected $client = null;
    protected $key = 'paystack';

    public function getClient($config = null)
    {
        if (! $this->client) {
            if (! $config) {
                $config = $this->getConfig();
            }

            if (empty($config) || empty($config['secret_key'])) {
                throw new Exception('Missing Paystack secret key.');
            }

            $this->client = $config['secret_key'];
        }

        return $this->client;
    }

    public function checkConfig($config)
    {
        if (empty($config['secret_key'])) {
            return [
                'status' => false,
                'message' => __('The Paystack secret key is required.')
            ];
        }

        try {
            $response = $this->makeRequest('GET', '/balance', [], $config);
            if (!$response['status']) {
                throw new Exception('Invalid Paystack credentials.');
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
        $amount = (int) round($order->getTotalAmount() * 100); // Paystack expects amount in Kobo
        $body = [
            'email' => $order->getUser()->email,
            'amount' => $amount,
            'currency' => $order->getCurrency(),
            'callback_url' => $order->getReturnUrl(),
            'metadata' => [
                'type' => $order->getSubjectType(),
                'id' => $order->id,
            ],
        ];

        try {
            $response = $this->makeRequest('POST', '/transaction/initialize', $body);

            return [
                'status' => true,
                'url' => $response['data']['authorization_url']
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
        $this->getLog()->info('Start Paystack ipn');
        $this->getLog()->info(print_r($data, true));

        $config = $this->getConfig();
        if (! $config) {
            return response('ok', 200);
        }

        // Validate Signature
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? null;
        if (!$signature || !$this->verifySignature($input, $signature, $config['secret_key'])) {
            $this->getLog()->info('Invalid signature');
            return response('ok', 200);
        }

        if ($data['event'] === 'charge.success') {
            $metadata = $data['data']['metadata'] ?? [];
            if (isset($metadata['type']) && isset($metadata['id'])) {
                $item = findByTypeId($metadata['type'], $metadata['id']);
                if ($item) {
                    $item->onSuccessful($data, $data['data']['reference']);
                }
            }
        }

        return response('ok', 200);
    }

    protected function makeRequest($method, $endpoint, $body = [], $config = null)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://api.paystack.co',
                'headers' => [
                    'Authorization' => 'Bearer ' . ($config ? $config['secret_key'] : $this->getClient()),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = $client->request($method, $endpoint, [
                'json' => $body,
                'verify' => false, // Nếu test local, thêm dòng này tạm thời
            ]);

            $result = json_decode($response->getBody(), true);

            if (!$result['status']) {
                throw new Exception($result['message'] ?? 'Paystack error');
            }

            return $result;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorBody = (string) $e->getResponse()?->getBody();
            $errorBody = json_decode($errorBody);
            throw new Exception('Paystack request failed: ' . $errorBody->message);
        }
    }

    protected function verifySignature($payload, $signature, $secret)
    {
        return hash_hmac('sha512', $payload, $secret) === $signature;
    }

    public function getMaxPrice()
    {
        return 10000000;
    }
}
