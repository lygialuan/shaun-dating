<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletSendNotification extends BaseNotification
{
    protected $type = 'wallet_send';
    protected $has_group = false;
    
    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        return __('sent you a payment.');
    }
}
