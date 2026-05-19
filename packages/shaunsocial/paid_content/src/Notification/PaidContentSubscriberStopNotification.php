<?php


namespace Packages\ShaunSocial\PaidContent\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PaidContentSubscriberStopNotification extends BaseNotification
{
    protected $type = 'paid_content_subscriber_stop';
    protected $has_group = false;

    public function getExtra()
    {
        $params = $this->notification->getParams();
        return [
            'subscription_id' => $params['subscription_id']
        ];
    }

    public function getHref()
    {
        return $this->notification->getUser()->getHref();
    }

    public function getMessage($count)
    {
        $subject = $this->notification->getSubject();
        return __('your subscription to :x has stopped',[
            'x' => $subject->getTitle()
        ]);
    }
}
