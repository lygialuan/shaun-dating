<?php

namespace Packages\ShaunSocial\UserVerify\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserVerifyRequestSendNotification extends BaseNotification
{
    protected $type = 'user_verify_request_send';
    protected $has_group = false;

    public function getExtra()
    {
        return [
            'url' => $this->getHref()
        ];
    }
    
    public function getHref()
    {
        $from = $this->notification->getFrom();
        if (! $from->isPage()) {
            return route('admin.user_verify.index').'?name='.$this->notification->getFrom()->user_name;
        } else {
            return route('admin.user_page.verify.index').'?name='.$this->notification->getFrom()->user_name;
        }
    }

    public function getMessage($count)
    {
        return __('submitted a verification request.');
    }
}
