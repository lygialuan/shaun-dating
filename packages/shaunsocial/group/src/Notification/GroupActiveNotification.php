<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class GroupActiveNotification extends BaseNotification
{
    protected $type = 'group_active';
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
        $subject = $this->notification->getSubject();
        return __("Your group ':1' is active now", ['1' => $subject->getTitle()]);
    }
}
