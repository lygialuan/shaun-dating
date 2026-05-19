<?php


namespace Packages\ShaunSocial\Core\Notification\Comment;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class CommentMentionReplyNotification extends BaseNotification
{
    protected $type = 'comment_mention_reply';

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
            return __("and others replied to a comment that you're mentioned in.");
        } else {
            return __("replied to a comment that you're mentioned in.");
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