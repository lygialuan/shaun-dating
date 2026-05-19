<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;
use Packages\ShaunSocial\Group\Models\GroupMember;

class GroupTransferOwnerNotification extends BaseNotification
{
    protected $type = 'group_transfer_owner';
    protected $has_group = false;

    public function getExtra()
    {
        $subject = $this->notification->getSubject();
        return [
            'group_id' => $subject->id,
            'slug' => $subject->slug
        ];
    }
    
    public function getHref()
    {
        $subject = $this->notification->getSubject();
        return $subject->getHref();
    }

    public function getMessage($count)
    {
        return __('transferred ownership of a group.');
    }

    public function checkExists()
    {
        $subject = $this->notification->getSubject();
        return GroupMember::checkOwner($this->notification->user_id, $subject->id);
    }
}