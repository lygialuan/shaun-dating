<?php 

namespace Packages\ShaunSocial\Core\Library\Sms;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Clickatell extends Base
{
    public function sendSms($text, $to)
    {
        $apiKey = $this->config['api_key'] ?? null;

        try {
            $response = Http::timeout(config('shaun_core.core.http_timeout'))->get('https://platform.clickatell.com/messages/http/send?apiKey='.$apiKey.'&to='.$to.'&content='.$text);
            $data = $response->json();
            Log::channel('shaun_sms')->info($response->body());
            if (! $data || $data['responseCode'] != '202') {
                return ['status' => false, 'message' => __('Something went wrong. Please try again later.')];
            }
        } catch (Exception $e) {
            Log::channel('shaun_sms')->info($e->getMessage());
            return ['status' => false, 'message' => __('Something went wrong. Please try again later.')];
        }

        return ['status' => true];
    }
}