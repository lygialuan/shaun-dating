<?php

namespace Packages\ShaunSocial\PaidContent\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PaidContentBuyPostNotification extends BaseNotification
{
    protected $type = 'paid_content_buy_post';
    protected $has_group = true;

    public function getExtra()
    {
        $subject = $this->notification->getSubject();

        return [
            'post_id' => $subject->id
        ];
    }
    
    public function getHref()
    {
        $subject = $this->notification->getSubject();
        return $subject->getHref();
    }

    public function getMessage($count)
    {
        return __("unlocked your paid post.");
    }
}
