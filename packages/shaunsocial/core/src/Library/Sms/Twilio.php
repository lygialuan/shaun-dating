<?php 

namespace Packages\ShaunSocial\Core\Library\Sms;

use Exception;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class Twilio extends Base
{
    public function sendSms($text, $to)
    {
        $sid = $this->config['sid'] ?? null;
        $token = $this->config['token'] ?? null;
        $from = $this->config['from'] ?? null;

        try {
            $client = new Client($sid, $token);

            $client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $text
                ]
            );
        } catch (Exception $e) {
            Log::channel('shaun_sms')->info($e->getMessage());
            return ['status' => false, 'message' => __('Something went wrong. Please try again later.')];
        }

        return ['status' => true];
    }
}