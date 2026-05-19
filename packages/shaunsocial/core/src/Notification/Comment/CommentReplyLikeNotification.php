<?php


namespace Packages\ShaunSocial\Core\Notification\Comment;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class CommentReplyLikeNotification extends BaseNotification
{
    protected $type = 'comment_reply_like';

    public function getExtra()
    {
        $reply = $this->notification->getSubject();
        $comment = $reply->getComment();

        return [
            'subject_id' => $comment->subject_id,
            'subject_type' => $comment->subject_type,
            'comment_id' => $comment->id,
            'reply_id' => $reply->id
        ];
    }

    public function getHref()
    {
        return $this->notification->getSubject()->getHref();
    }

    public function getMessage($count)
    {
        if ($count > 1) {
            return __("and others reacted to your reply.");
        } else {
            return __("reacted to your reply.");
        }
    }

    public function checkExists()
    {
        $reply = $this->notification->getSubject();
        if (! $reply) {
            return false;
        }

        $comment = $reply->getComment();
        if (! $comment) {
            return false;
        }

        if (! $comment->getSubject()) {
            return false;
        }

        return true;
    }
}
