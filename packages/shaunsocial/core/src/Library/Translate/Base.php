<?php 

namespace Packages\ShaunSocial\Core\Library\Translate;

class Base
{
    protected $config;

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    public function translate($text, $language)
    {
        return ['status' => false, 'message' => ''];
    }
}