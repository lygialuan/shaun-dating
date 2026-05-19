<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletTipNotification extends BaseNotification
{
    protected $type = 'wallet_tip';
    protected $has_group = false;
    
    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        $user = $this->notification->getSubject();
        if ($user) {
            $user = getUserIncludeDelete($user);
            $name = $user->getTitle();
        }

        return __('you received a tip from :1.',['1' => $name]);
    }
}
