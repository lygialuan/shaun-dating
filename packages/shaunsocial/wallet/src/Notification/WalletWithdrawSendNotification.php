<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletWithdrawSendNotification extends BaseNotification
{
    protected $type = 'wallet_withdraw_send';
    protected $has_group = false;

    public function getExtra()
    {
        return [
            'url' => $this->getHref()
        ];
    }
    
    public function getHref()
    {
        return route('admin.wallet.withdraw.index').'?name='.$this->notification->getFrom()->user_name;
    }

    public function getMessage($count)
    {
        return __('sent a payment request.');
    }
}
