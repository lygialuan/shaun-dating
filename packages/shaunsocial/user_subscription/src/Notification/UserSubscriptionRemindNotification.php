<?php


namespace Packages\ShaunSocial\UserSubscription\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserSubscriptionRemindNotification extends BaseNotification
{
    protected $type = 'user_subscription_remind';
    protected $has_group = false;

    public function getHref()
    {
        return route('web.user_subscription.index');
    }

    public function getMessage($count)
    {
        $params = $this->notification->getParams();
        return __('your membership subscription will auto renew in next :x day(s).',[
            'x' => $params['remind_day']
        ]);
    }
}
