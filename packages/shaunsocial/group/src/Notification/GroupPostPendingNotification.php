<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class GroupPostPendingNotification extends BaseNotification
{
    protected $type = 'group_post_pending';
    protected $has_group = false;

    public function getExtra()
    {
        $subject = $this->notification->getSubject();
        return [
            'group_id' => $subject->group_id,
            'post_id' => $subject->post_id
        ];
    }
    
    public function getHref()
    {
        $subject = $this->notification->getSubject();
        return route('web.group.admin_pending_post', ['id' => $subject->group_id, 'post_id' => $subject->post_id]);
    }

    public function getMessage($count)
    {
        $subject = $this->notification->getSubject();
        $group = $subject->getGroup();
        return __("new post in ':1' that needs your approval.", ['1' => $group->getTitle()]);
    }

    public function checkExists()
    {
        $subject = $this->notification->getSubject();
        $post = $subject->getPost();
        $group = $subject->getGroup();

        return ($post && $group);
    }
}
