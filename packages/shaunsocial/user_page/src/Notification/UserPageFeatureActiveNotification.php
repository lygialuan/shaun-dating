<?php


namespace Packages\ShaunSocial\UserPage\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserPageFeatureActiveNotification extends BaseNotification
{
    protected $type = 'page_feature_active';
    protected $has_group = false;

    public function getExtra()
    {
        $user = $this->notification->getUser();
        return [
            'user_id' => $user->id,
            'user_name' => $user->user_name
        ];
    }

    public function getHref()
    {
        return $this->notification->getUser()->getHref();
    }

    public function getMessage($count)
    {
        return __('is featured now.');
    }
}
