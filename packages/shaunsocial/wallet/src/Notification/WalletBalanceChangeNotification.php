<?php

namespace Packages\ShaunSocial\Wallet\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class WalletBalanceChangeNotification extends BaseNotification
{
    protected $type = 'wallet_balance_change';
    protected $has_group = false;
    
    public function getHref()
    {
        return route('web.wallet.index');
    }

    public function getMessage($count)
    {
        $subject = $this->notification->getSubject();
        $name = setting('site.title');
        $user = $subject->getFromUser();
        if ($user) {
            $user = getUserIncludeDelete($user);
            $name = $user->getTitle();
        }

        if ($subject->amount > 0) {
            return __('you received a payment from :1.',['1' => $name]);
        } else {
            return __('you sent a payment to :1.', ['1' => $name]);
        }
    }
}
