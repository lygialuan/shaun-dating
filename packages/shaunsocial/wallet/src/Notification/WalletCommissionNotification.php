<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletCommissionNotification extends BaseNotification
{
    protected $type = 'wallet_commission';
    protected $has_group = false;
    
    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        return __('you received a referral commission from :1.',['1' => setting('site.title')]);
    }
}
