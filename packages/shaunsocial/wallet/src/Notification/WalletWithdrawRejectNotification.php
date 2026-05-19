<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletWithdrawRejectNotification extends BaseNotification
{
    protected $type = 'wallet_withdraw_reject';
    protected $has_group = false;

    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        return __('Your payment request has been rejected.');
    }
}
