<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletWithdrawAcceptNotification extends BaseNotification
{
    protected $type = 'wallet_withdraw_accept';
    protected $has_group = false;

    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        return __('Your payment request has been accepted.');
    }
}
