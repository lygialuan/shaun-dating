<?php


namespace Packages\ShaunSocial\UserPage\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserPageReviewNotification extends BaseNotification
{
    protected $type = 'page_review';
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
        return __('wrote a recommendation.');
    }
}
