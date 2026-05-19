<?php

namespace Packages\ShaunSocial\Gift\Wallet\Payment;

use Packages\ShaunSocial\Wallet\Wallet\Payment\WalletPaymentTypeBase;

class GiftFee extends WalletPaymentTypeBase
{
    public function getDescription($transaction)
    {
        if ($transaction->amount > 0) {
            return __('Fee charged to :user for receiving a gift', [
                'user' => $transaction->getFromUser()->name
            ]);
        }
    }
}
