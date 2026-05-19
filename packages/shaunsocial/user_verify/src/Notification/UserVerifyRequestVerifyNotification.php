<?php

namespace Packages\ShaunSocial\UserVerify\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserVerifyRequestVerifyNotification extends BaseNotification
{
    protected $type = 'user_verify_request_verify';
    protected $has_group = false;
    
    public function getExtra()
    {
        return [
            'user_name' => $this->notification->getUser()->user_name,
        ];
    }

    public function getHref()
    {
        return $this->notification->getUser()->getHref();
    }

    public function getMessage($count)
    {
        return __('Your profile is verified.');
    }
}
