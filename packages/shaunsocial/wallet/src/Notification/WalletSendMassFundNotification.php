<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletSendMassFundNotification extends BaseNotification
{
    protected $type = 'wallet_send_mass_fund';
    protected $has_group = false;
    
    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        return __('added funds to your eWallet.');
    }
}
