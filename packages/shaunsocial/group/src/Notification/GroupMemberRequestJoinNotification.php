<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class GroupMemberRequestJoinNotification extends BaseNotification
{
    protected $type = 'group_member_request_join';
    protected $has_group = false;

    public function getExtra()
    {
        $subject = $this->notification->getSubject();
        return [
            'group_id' => $subject->group_id,
            'user_id' => $subject->user_id
        ];
    }
    
    public function getHref()
    {
        $subject = $this->notification->getSubject();
        return route('web.group.member_request_join', ['id' => $subject->group_id, 'user_id' => $subject->user_id]);
    }

    public function getMessage($count)
    {
        $subject = $this->notification->getSubject();
        $group = $subject->getGroup();
        return __('requested to join')." '".$group->getTitle().".";
    }

    public function checkExists()
    {
        $subject = $this->notification->getSubject();
        $user = $subject->getUser();
        $group = $subject->getGroup();

        return ($user && $group);
    }
}
