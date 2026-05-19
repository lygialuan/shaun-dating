<?php

namespace Packages\ShaunSocial\Gateway\Repositories\Helpers;

use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Exception;

class RazorPay extends GatewayBase
{
    protected $currencyList = ['INR', 'USD'];
    protected $client = null;
    protected $key = 'razorpay';

    public function getClient($config = null)
    {
        if (! $this->client) {
            if (! $config) {
                $config = $this->getConfig();
            }

            if (empty($config['key_id']) || empty($config['key_secret'])) {
                throw new Exception('Missing Razorpay credentials.');
            }

            $this->client = new Api($config['key_id'], $config['key_secret']);
        }

        return $this->client;
    }

    public function checkConfig($config)
    {
        if (empty($config['key_id']) || empty($config['key_secret'])) {
            return [
                'status' => false,
                'message' => __('The Razorpay key_id and key_secret are required.')
            ];
        }

        try {
            $client = new Api($config['key_id'], $config['key_secret']);
            $client->payment->all(['count' => 1]); // test call
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }

        return ['status' => true];
    }

    public function createLinkPayment($order)
    {
        $config = $this->getConfig();
        $client = $this->getClient($config);

        $razorOrder = $client->order->create([
            'receipt' => (string) $order->id,
            'amount' => (int) round($order->getTotalAmount(), 2) * 100,
            'currency' => $order->getCurrency(),
            'payment_capture' => 1,
            'notes' => [
                'type' => $order->getSubjectType(),
                'id' => $order->id
            ]
        ]);

        return [
            'status' => true,
            'url' => route('razorpay.pay', ['order_id' => $razorOrder['id']])
        ];
    }

    public function ipn()
    {
        Log::channel('ipnrzp')->info('start');
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        $this->getLog()->info('Start Razorpay IPN');
        $this->getLog()->info(print_r($data, true));

        $config = $this->getConfig();
        if (! $config || empty($config['webhook_secret'])) {
            return response('ok', 200);
        }

        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? null;
        if (!$signature || !$this->verifySignature($input, $signature, $config['webhook_secret'])) {
            $this->getLog()->info('Invalid Razorpay signature');
            return response('ok', 200);
        }

        if (isset($data['event']) && $data['event'] === 'payment.captured') {
            $metadata = $data['payload']['payment']['entity']['notes'] ?? [];
            if (isset($metadata['type']) && isset($metadata['id'])) {
                $item = findByTypeId($metadata['type'], $metadata['id']);
                if ($item) {
                    $item->onSuccessful($data, $data['payload']['payment']['entity']['id']);
                }
            }
        }
        Log::channel('ipnrzp')->info('end');
        return response('ok', 200);
    }

    protected function verifySignature($payload, $signature, $secret)
    {
        return hash_hmac('sha256', $payload, $secret) === $signature;
    }

    public function getMaxPrice()
    {
        return 10000000;
    }
}
