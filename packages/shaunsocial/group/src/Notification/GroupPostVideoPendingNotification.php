<?php


namespace Packages\ShaunSocial\Group\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class GroupPostVideoPendingNotification extends BaseNotification
{
    protected $type = 'group_post_video_pending';
    protected $has_group = false;

    public function getExtra()
    {
        return [
            'group_id' => $this->notification->subject_id,
        ];
    }

    public function getHref()
    {
        return $this->notification->getSubject()->getHref();
    }

    public function getMessage($count)
    {
        return __('Your video is ready to view in the list of unapproved posts.');
    }
}
