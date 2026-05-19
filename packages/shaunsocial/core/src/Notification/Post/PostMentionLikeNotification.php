<?php


namespace Packages\ShaunSocial\Core\Notification\Post;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PostMentionLikeNotification extends BaseNotification
{
    protected $type = 'post_mention_like';

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
            return __("and others reacted to a post that you're mentioned in.");
        } else {
            return __("reacted to a post that you're mentioned in.");
        }
    }
}
