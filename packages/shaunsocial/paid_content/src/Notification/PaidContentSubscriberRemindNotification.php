<?php


namespace Packages\ShaunSocial\PaidContent\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PaidContentSubscriberRemindNotification extends BaseNotification
{
    protected $type = 'paid_content_subscriber_remind';
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
        $params = $this->notification->getParams();
        $subject = $this->notification->getSubject();
        return __('your subscription to :1 will auto renew in next :2 day(s).',[
            '1' => $subject->getTitle(),
            '2' => $params['remind_day'],
        ]);
    }
}
