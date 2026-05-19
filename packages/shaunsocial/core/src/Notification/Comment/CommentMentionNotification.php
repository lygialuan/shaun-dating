<?php


namespace Packages\ShaunSocial\Core\Notification\Comment;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class CommentMentionNotification extends BaseNotification
{
    protected $type = 'comment_mention';
    protected $has_group = false;

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
        return __('mentioned you in a comment.');
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
