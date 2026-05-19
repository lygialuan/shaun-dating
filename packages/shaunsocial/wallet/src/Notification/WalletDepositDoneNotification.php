<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletDepositDoneNotification extends BaseNotification
{
    protected $type = 'wallet_deposit_done';
    protected $has_group = false;
    
    public function getHref()
    {
        return route('web.wallet.index');
    }
    
    public function getMessage($count)
    {
        return __('Your account has been successfully credited.');
    }
}
