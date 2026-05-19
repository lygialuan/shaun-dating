<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;
use Packages\ShaunSocial\Group\Enum\GroupMemberRole;
use Packages\ShaunSocial\Group\Models\GroupMember;

class GroupAddAdminNotification extends BaseNotification
{
    protected $type = 'group_add_admin';
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
        return __('added you as moderator of a group.');
    }

    public function checkExists()
    {
        $subject = $this->notification->getSubject();
        $member = GroupMember::getMember($this->notification->user_id, $subject->id);
        
        return $member && $member->role == GroupMemberRole::ADMIN;
    }
}