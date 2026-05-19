<?php


namespace Packages\ShaunSocial\Core\Notification\Post;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PostMentionCommentNotification extends BaseNotification
{
    protected $type = 'post_mention_comment';

    public function getExtra()
    {
        $comment = $this->notification->getSubject();
        return [
            'comment_id' => $comment->id,
            'post_id' => $comment->subject_id
        ];
    }

    public function getHref()
    {
        return $this->notification->getSubject()->getHref();
    }

    public function getMessage($count)
    {
        if ($count > 1) {
            return __("and others commented on a post that you're mentioned in.");
        } else {
            return __("commented on a post that you're mentioned in.");
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
