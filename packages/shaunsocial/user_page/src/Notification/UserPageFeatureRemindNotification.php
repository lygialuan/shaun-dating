<?php


namespace Packages\ShaunSocial\UserPage\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserPageFeatureRemindNotification extends BaseNotification
{
    protected $type = 'page_feature_remind';
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
        return __('featured subscription for your page will auto-renew in the next :x day(s).',[
            'x' => $params['remind_day']
        ]);
    }
}
