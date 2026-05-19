<?php


namespace Packages\ShaunSocial\UserPage\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserPageNotifyToAdminNotification extends BaseNotification
{
    protected $type = 'page_notify_to_admin';
    protected $has_group = false;

    public function getExtra()
    {
        $user = $this->notification->getFrom();
        return [
            'user_id' => $user->id,
            'user_name' => $user->user_name
        ];
    }

    public function getHref()
    {
        return $this->notification->getFrom()->getHref();
    }

    public function getMessage($count)
    {
        return __('got a new notification.');
    }
}
