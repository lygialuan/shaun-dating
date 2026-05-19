<?php


namespace Packages\ShaunSocial\Core\Notification\Follow;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class FollowNewNotification extends BaseNotification
{
    protected $type = 'follow_new';

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
        return $this->notification->getSubject()->getHref();
    }

    public function getMessage($count)
    {
        if ($count > 1) {
            return __("and others followed you.");
        } else {
            return __("followed you.");
        }
    }
}
