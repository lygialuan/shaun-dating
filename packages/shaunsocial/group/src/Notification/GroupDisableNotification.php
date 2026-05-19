<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class GroupDisableNotification extends BaseNotification
{
    protected $type = 'group_disable';
    protected $has_group = false;
    
    public function getHref()
    {
        route('web.group.list_group');
    }

    public function getMessage($count)
    {
        $subject = $this->notification->getSubject();
        return __("Your group ':1' is disabled now", ['1' => $subject->getTitle()]);
    }
}
