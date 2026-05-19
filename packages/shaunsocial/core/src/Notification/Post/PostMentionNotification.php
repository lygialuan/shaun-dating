<?php


namespace Packages\ShaunSocial\Core\Notification\Post;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PostMentionNotification extends BaseNotification
{
    protected $type = 'post_mention';
    protected $has_group = false;

    public function getExtra()
    {
        return [
            'post_id' => $this->notification->subject_id,
        ];
    }

    public function getHref()
    {
        return $this->notification->getSubject()->getHref();
    }

    public function getMessage($count)
    {
        return __("mentioned you in a post.");
    }
}
