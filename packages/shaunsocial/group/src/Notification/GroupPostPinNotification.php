<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class GroupPostPinNotification extends BaseNotification
{
    protected $type = 'group_post_pin';
    protected $has_group = false;

    public function getExtra()
    {
        $subject = $this->notification->getSubject();
        $group = $subject->getSource();
        return [
            'group_id' => $subject->source_id,
            'slug' => $group->slug
        ];
    }
    
    public function getHref()
    {
        $subject = $this->notification->getSubject();
        $group = $subject->getSource();
        return $group->getHref();
    }

    public function getMessage($count)
    {
        $subject = $this->notification->getSubject();
        $group = $subject->getSource();
        return __('new pinned post in')." '".$group->getTitle()."'.";
    }

    public function checkExists()
    {
        $subject = $this->notification->getSubject();
        $group = $subject->getSource();

        return $group;
    }
}
