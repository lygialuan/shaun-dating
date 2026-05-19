<?php


namespace Packages\ShaunSocial\Core\Notification\Post;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class PostCommentOfCommentNotification extends BaseNotification
{
    protected $type = 'post_comment_of_comment';
    protected $has_group = false;

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
        return __("commented on the post that you're commented on.");
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
