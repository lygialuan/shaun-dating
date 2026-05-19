<?php

namespace Packages\ShaunSocial\PaidContent\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PaidContentSubscriberNotification extends BaseNotification
{
    protected $type = 'paid_content_subscriber';
    protected $has_group = true;

    public function getExtra()
    {
        $user = $this->notification->getSubject();

        return [
            'user_id' => $user->id,
            'user_name' => $user->user_name
        ];
    }
    
    public function getHref()
    {
        $subject = $this->notification->getSubject();
        return $subject->getHref();
    }

    public function getMessage($count)
    {
        return __("subscribed to your profile.");
    }
}
