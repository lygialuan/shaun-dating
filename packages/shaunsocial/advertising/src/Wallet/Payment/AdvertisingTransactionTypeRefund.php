<?php


namespace Packages\ShaunSocial\Advertising\Wallet\Payment;

use Packages\ShaunSocial\Wallet\Wallet\Payment\WalletPaymentTypeBase;

class AdvertisingTransactionTypeRefund extends WalletPaymentTypeBase
{
    public function getDescription($transaction)
    {
        if ($transaction->amount < 0) {
            return __('Refund for Ads campaign');
        }
        return __('Refunded from Ads campaign');
    }
}
