<?php


namespace Packages\ShaunSocial\PaidContent\Wallet\Payment;

use Packages\ShaunSocial\Wallet\Wallet\Payment\WalletPaymentTypeBase;

class PaidContentTransactionTypeTip extends WalletPaymentTypeBase
{
    public function getDescription($transaction)
    {
        $user = $transaction->getSubject();
        $user = getUserIncludeDelete($user);

        if ($transaction->amount > 0) {
            return __('Receive a tip from'). ' '. $user->getName();
        }
        
        return __('Send a tip to').' '. $user->getName();
    }
}
