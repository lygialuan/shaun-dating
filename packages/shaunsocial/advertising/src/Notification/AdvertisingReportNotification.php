<?php

namespace Packages\ShaunSocial\Advertising\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class AdvertisingReportNotification extends BaseNotification
{
    protected $type = 'advertising_report';
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

        return __('Yesterday your ads led to :view_count view(s) and :click_count click(s).', [
            'view_count' => $params['view_count'],
            'click_count' => $params['click_count']
        ]);
    }
}
