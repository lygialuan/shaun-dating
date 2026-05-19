<?php


namespace Packages\ShaunSocial\Story\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;
use Packages\ShaunSocial\Story\Models\StoryView;

class StoryEndNotification extends BaseNotification
{
    protected $type = 'story_end';

    public function getExtra()
    {
        return [
            'story_item_id' => $this->notification->subject_id,
        ];
    }

    public function getHref()
    {
        return $this->notification->getSubject()->getHref();
    }

    public function getMessage($count)
    {
        $subject = $this->notification->getSubject();
        $count = StoryView::getCount($subject->user_id, $subject->id);

        if ($count == 1) {
            return __('Your story got :number view before it expired.', ['number' => $count]);
        } else {
            return __('Your story got :number views before it expired.', ['number' => $count]);
        }
    }


}
