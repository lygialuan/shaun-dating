<?php

namespace Packages\ShaunSocial\Advertising\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class AdvertisingActiveNotification extends BaseNotification
{
    protected $type = 'advertising_active';
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
        return __('Your ad campaign is active now.');
    }
}
