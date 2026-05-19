<?php

namespace Packages\ShaunSocial\Dating\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class DatingUserLikeNotification extends BaseNotification
{
    protected $type = 'dating_user_like';
    protected $has_group = false;


    public function getMessage($count)
    {
        return __('liked you');
    }
}
