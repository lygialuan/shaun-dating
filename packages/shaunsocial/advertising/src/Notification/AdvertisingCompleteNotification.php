<?php

namespace Packages\ShaunSocial\Advertising\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class AdvertisingCompleteNotification extends BaseNotification
{
    protected $type = 'advertising_complete';
    protected $has_group = false;

    public function getExtra()
    {
        $advertising = $this->notification->getSubject();
        return [
            'id' => $advertising->id,
        ];
    }

    public function getHref()
    {
        return route('web.advertising.index');
    }

    public function getMessage($count)
    {
        return __('Your ads is completed.');
    }
}
