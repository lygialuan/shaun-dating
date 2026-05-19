<?php

namespace Packages\ShaunSocial\Advertising\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class AdvertisingAdminChangeStatusNotification extends BaseNotification
{
    protected $type = 'advertising_admin_change_status';
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
        $params = $this->notification->getParams();
        switch ($params['status']) {
            case 'stop':
                return __('stopped your ads.');
                break;
            case 'enable':
                return __('enabled completed your ads.');
                break;
            case 'complete':
                return __('completed your ads.');
                break;
        }
    }
}
