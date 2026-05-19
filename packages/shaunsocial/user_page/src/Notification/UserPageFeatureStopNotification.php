<?php


namespace Packages\ShaunSocial\UserPage\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserPageFeatureStopNotification extends BaseNotification
{
    protected $type = 'page_feature_stop';
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
        return __('featured subscription for your page has stopped.');
    }
}
