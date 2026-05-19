<?php

namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Exception;

class Flutterwave extends GatewayBase
{
    protected $currencyList = ['NGN', 'GHS', 'ZAR', 'KES', 'USD'];
    protected $client = null;
    protected $key = 'flutterwave';

    public function getClient($config = null)
    {
        if (! $this->client) {
            if (! $config) {
                $config = $this->getConfig();
            }

            if (empty($config) || empty($config['secret_key'])) {
                throw new Exception('Missing Flutterwave secret key.');
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
                'message' => __('The Flutterwave secret key is required.')
            ];
        }

        try {
            $response = $this->makeRequest('GET', '/v3/bill-categories', [], $config);
            if (!isset($response['status']) || $response['status'] !== 'success') {
                throw new Exception('Invalid Flutterwave credentials.');
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
        $body = [
            'tx_ref' => uniqid('shaun_', true),
            'amount' => $amount,
            'currency' => $order->getCurrency() ?: 'NGN',
            'redirect_url' => $order->getReturnUrl(),
            'customer' => [
                'email' => $order->getUser()->email,
                'name' => $order->getUser()->getTitle(),
            ],
            'customizations' => [
                'title' => 'Payment for ' . $order->getSubjectType() . ' #' . $order->id,
                'description' => 'SHAUNSOCIAL Checkout',
            ],
            'meta' => [
                'type' => $order->getSubjectType(),
                'id' => $order->id,
            ]
        ];

        try {
            $response = $this->makeRequest('POST', '/v3/payments', $body);

            return [
                'status' => true,
                'url' => $response['data']['link'] ?? null
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
        $request = request();
        $input = $request->getContent();
        $this->getLog()->info('Start Flutterwave IPN', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'signature' => $request->header('verif-hash'),
        ]);
        $this->getLog()->info('Raw Flutterwave IPN payload', ['body' => $input]);

        $data = json_decode($input, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->getLog()->warning('Flutterwave IPN received invalid JSON', [
                'error' => json_last_error_msg(),
            ]);
            return response('ok', 200);
        }
        $this->getLog()->info('Parsed Flutterwave IPN', $data);

        $config = $this->getConfig();
        if (! $config) {
            $this->getLog()->warning('Flutterwave IPN skipped: missing gateway config');
            return response('ok', 200);
        }

        // No signature by default, but can check tx_ref & secret optionally

        $payload = $data['data'] ?? $data;
        $status = $payload['status'] ?? ($data['status'] ?? null);
        $metadata = $payload['meta'] ?? ($payload['meta_data'] ?? ($data['meta'] ?? ($data['meta_data'] ?? [])));

        if ($status === 'successful') {
            $this->getLog()->info('Flutterwave IPN marked successful', [
                'tx_ref' => $payload['tx_ref'] ?? null,
                'id' => $payload['id'] ?? null,
                'event' => $data['event'] ?? null,
            ]);
            if (isset($metadata['type']) && isset($metadata['id'])) {
                $item = findByTypeId($metadata['type'], $metadata['id']);
                if ($item) {
                    $item->onSuccessful($payload, $payload['tx_ref']);
                    $this->getLog()->info('Flutterwave IPN processed item', [
                        'type' => $metadata['type'],
                        'id' => $metadata['id'],
                    ]);
                } else {
                    $this->getLog()->warning('Flutterwave IPN could not find item', $metadata);
                }
            } else {
                $this->getLog()->warning('Flutterwave IPN missing metadata', $metadata);
            }
        } else {
            $this->getLog()->warning('Flutterwave IPN received non-success status', [
                'status' => $status,
                'tx_ref' => $payload['tx_ref'] ?? null,
            ]);
        }

        return response('ok', 200);
    }

    protected function makeRequest($method, $endpoint, $body = [], $config = null)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://api.flutterwave.com',
                'headers' => [
                    'Authorization' => 'Bearer ' . ($config ? $config['secret_key'] : $this->getClient()),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = $client->request($method, $endpoint, [
                'json' => $body,
                'verify' => false,
            ]);

            $result = json_decode($response->getBody(), true);

            if (!isset($result['status']) || $result['status'] !== 'success') {
                throw new Exception($result['message'] ?? 'Flutterwave error');
            }

            return $result;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorBody = (string) $e->getResponse()?->getBody();
            throw new Exception('Flutterwave request failed: ' . $errorBody);
        }
    }

    public function getMaxPrice()
    {
        return 10000000;
    }
}
