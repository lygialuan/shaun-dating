<?php


namespace Packages\ShaunSocial\UserPage\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserPageTransferOwnerNotification extends BaseNotification
{
    protected $type = 'page_transfer_owner';
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
        return __('transferred ownership of a page.');
    }
}
