<?php


namespace Packages\ShaunSocial\Advertising\Wallet\Payment;

use Packages\ShaunSocial\Wallet\Wallet\Payment\WalletPaymentTypeBase;

class AdvertisingTransactionTypeBuy extends WalletPaymentTypeBase
{
    public function getDescription($transaction)
    {
        if ($transaction->amount > 0) {
            return __('Payment received for Ads campaign');
        }
        return __('Payment sent for Ads campaign');
    }
}
