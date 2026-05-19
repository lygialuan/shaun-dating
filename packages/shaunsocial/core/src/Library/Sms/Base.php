<?php 

namespace Packages\ShaunSocial\Core\Library\Sms;

class Base
{
    protected $config;

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    public function sendSms($text, $to)
    {
        return ['status' => true];
    }
}