<?php


namespace Packages\ShaunSocial\Core\Notification\Invite;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class InviteJoinNotification extends BaseNotification
{
    protected $type = 'invite_join';
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
        return __('joined from your invitation.');
    }
}
