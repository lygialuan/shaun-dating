<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WallelBalanceNotifyNotification extends BaseNotification
{
    protected $type = 'wallet_balance_notify';
    protected $has_group = false;

    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        return __('Your account balance is running low.');
    }
}
