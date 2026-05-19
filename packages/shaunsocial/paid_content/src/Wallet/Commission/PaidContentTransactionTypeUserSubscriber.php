<?php


namespace Packages\ShaunSocial\PaidContent\Wallet\Commission;

use Packages\ShaunSocial\Wallet\Wallet\Payment\WalletPaymentTypeBase;

class PaidContentTransactionTypeUserSubscriber extends WalletPaymentTypeBase
{
    public function getDescription($transaction)
    {
        $subject = $transaction->getSubject();
        $user = $subject->getSubject();
        $user = getUserIncludeDelete($user);

        return __("Receive referral commission from :1's exclusive content subscription", ['1' => $user->getTitle()]);
    }
}