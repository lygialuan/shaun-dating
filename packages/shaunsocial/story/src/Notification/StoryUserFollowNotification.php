<?php


namespace Packages\ShaunSocial\Story\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class StoryUserFollowNotification extends BaseNotification
{
    protected $type = 'story_user_follow';
    protected $has_group = false;

    public function getExtra()
    {
        return [
            'story_item_id' => $this->notification->subject_id,
        ];
    }

    public function getHref()
    {
        return $this->notification->getSubject()->getHref();
    }

    public function getMessage($count)
    {
        return __("created a new story.");
    }
}
