<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletSendGiftNotification extends BaseNotification
{
    protected $type = 'wallet_send_gift';
    protected $has_group = false;
    
    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        return __('just sent you a gift.');
    }
}
