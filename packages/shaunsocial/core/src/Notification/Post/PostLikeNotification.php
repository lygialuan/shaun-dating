<?php


namespace Packages\ShaunSocial\Core\Notification\Post;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PostLikeNotification extends BaseNotification
{
    protected $type = 'post_like';

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
        if ($count > 1) {
            return __("and others reacted to your post.");
        } else {
            return __("reacted to your post.");
        }
    }
}
