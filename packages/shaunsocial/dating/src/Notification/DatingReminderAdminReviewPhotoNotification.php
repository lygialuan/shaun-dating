<?php

namespace Packages\ShaunSocial\Dating\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class DatingReminderAdminReviewPhotoNotification extends BaseNotification
{
    protected $type = 'dating_reminder_admin_review_photo';
    protected $has_group = false;


    public function getExtra()
    {
        return [
            'url' => $this->getHref()
        ];
    }
    
    public function getHref()
    {
        return route('admin.dating.profile_pictures');
    }

    public function getMessage($count)
    {
        return __('Admin you have some member profile photos need to review.');
    }
}
