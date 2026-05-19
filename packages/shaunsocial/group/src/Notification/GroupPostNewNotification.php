<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class GroupPostNewNotification extends BaseNotification
{
    protected $type = 'group_post_new';
    protected $has_group = false;

    public function getExtra()
    {
        $subject = $this->notification->getSubject();
        return [
            'post_id' => $subject->id
        ];
    }
    
    public function getHref()
    {
        $subject = $this->notification->getSubject();
        return $subject->getHref();
    }

    public function getMessage($count)
    {
        $subject = $this->notification->getSubject();
        $group = $subject->getSource();
        return __('create a new post in')." '".$group->getTitle()."'.";
    }

    public function checkExists()
    {
        $subject = $this->notification->getSubject();

        return $subject;
    }
}
