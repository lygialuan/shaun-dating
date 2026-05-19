<?php


namespace Packages\ShaunSocial\UserPage\Wallet\Payment;

use Packages\ShaunSocial\Wallet\Wallet\Payment\WalletPaymentTypeBase;

class UserPageTransactionTypeBuyFeature extends WalletPaymentTypeBase
{
    public function getDescription($transaction)
    {
        if ($transaction->amount > 0) {
            return __('Payment received for feature page');
        }
        return __('Buy feature package for page');
    }
}
