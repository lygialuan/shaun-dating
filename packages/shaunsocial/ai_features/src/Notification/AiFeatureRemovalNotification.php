<?php

namespace Packages\ShaunSocial\AiFeatures\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class AiFeatureRemovalNotification extends BaseNotification
{
    protected $type = 'ai_feature_auto_removal';
   
    public function getExtra()
    {
        $task = $this->notification->getSubject();
        return [
            'id' => $task->id,
        ];
    }

    public function getMessage($count)
    {
        $task = $this->notification->getSubject();
        $type = $task->subject_type ?? null;
        return match ($type) {
            'posts', 'post' => __('Your post was removed because it violates our community standards.'),
            'comments', 'comment' => __('Your comment was removed because it violates our community standards.'),
            'comment_replies', 'comment_reply' => __('Your comment reply was removed because it violates our community standards.'),
            default => __('Your content was removed because it violates our community standards.'),
        };
    }

    public function getHref()
    {
        $task = $this->notification->getSubject();
        return route('web.compliance.notice', ['task' => $task->id]);
    }

}
