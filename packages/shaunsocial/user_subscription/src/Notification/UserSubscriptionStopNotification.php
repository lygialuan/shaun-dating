<?php


namespace Packages\ShaunSocial\UserSubscription\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserSubscriptionStopNotification extends BaseNotification
{
    protected $type = 'user_subscription_stop';
    protected $has_group = false;

    public function getHref()
    {
        return route('web.user_subscription.index');
    }

    public function getMessage($count)
    {
        return __('your membership subscription has stopped.');
    }
}
