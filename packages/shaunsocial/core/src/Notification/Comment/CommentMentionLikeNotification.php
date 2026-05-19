<?php


namespace Packages\ShaunSocial\Core\Notification\Comment;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class CommentMentionLikeNotification extends BaseNotification
{
    protected $type = 'comment_mention_like';

    public function getHref()
    {
        return $this->notification->getSubject()->getHref();
    }

    public function getExtra() 
    {
        $comment = $this->notification->getSubject();
        
        return [
            'subject_id' => $comment->subject_id,
            'subject_type' => $comment->subject_type,
            'comment_id' => $comment->id
        ];
    }

    public function getMessage($count)
    {
        if ($count > 1) {
            return __("and others reacted to a comment that you're mentioned in.");
        } else {
            return __("reacted to a comment that you're mentioned in.");
        }
    }

    public function checkExists()
    {
        $comment = $this->notification->getSubject();
        if (! $comment) {
            return false;
        }

        if (! $comment->getSubject()) {
            return false;
        }

        return true;
    }
}
