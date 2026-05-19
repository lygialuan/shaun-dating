<?php

namespace Packages\ShaunSocial\Dating\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class DatingAdminUpdateProfileNotification extends BaseNotification
{
    protected $type = 'dating_admin_update_profile';
    protected $has_group = false;


    public function getMessage($count)
    {
        return __('Admin updated your profile photos.');
    }
}
