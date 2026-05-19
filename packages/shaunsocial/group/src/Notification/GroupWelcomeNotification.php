<?php

namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;
use Packages\ShaunSocial\Group\Models\GroupMember;

class GroupWelcomeNotification extends BaseNotification
{
    protected $type = 'group_welcome';
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
        return __('Welcome to').' '.$subject->getTitle();
    }

    public function checkExists()
    {
        $subject = $this->notification->getSubject();
        return GroupMember::getMember($this->notification->user_id, $subject->id);
    }
}
