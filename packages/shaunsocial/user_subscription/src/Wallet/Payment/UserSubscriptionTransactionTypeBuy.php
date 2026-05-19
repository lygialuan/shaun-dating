<?php


namespace Packages\ShaunSocial\UserSubscription\Wallet\Payment;

use Packages\ShaunSocial\Wallet\Wallet\Payment\WalletPaymentTypeBase;

class UserSubscriptionTransactionTypeBuy extends WalletPaymentTypeBase
{
    public function getDescription($transaction)
    {
        if ($transaction->amount > 0) {
            return __('Payment received for membership');
        }
        return __('Buy membership plan');
    }
}
