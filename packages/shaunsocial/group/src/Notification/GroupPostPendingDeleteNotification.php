<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class GroupPostPendingDeleteNotification extends BaseNotification
{
    protected $type = 'group_post_pending_delete';
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
        return __('rejected your post in a group.');
    }
}
