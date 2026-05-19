<?php


namespace Packages\ShaunSocial\Core\Notification;

class BaseNotification
{
    protected $type = '';
    protected $notification;
    protected $has_group = true;

    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getExtra()
    {
        return [];
    }

    public function getHref()
    {
        
    }

    public function getMessage($count)
    {

    }

    public function checkExists()
    {
        return true;
    }

    public function getPayloadData()
    {
        return $this->getExtra();
    }

    public function hasGroup()
    {
        return $this->has_group;
    }
}
