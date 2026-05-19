<?php


namespace Packages\ShaunSocial\UserPage\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserPageAddAdminNotification extends BaseNotification
{
    protected $type = 'page_add_admin';
    protected $has_group = false;

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
        return __('added you as moderator of a page.');
    }
}
