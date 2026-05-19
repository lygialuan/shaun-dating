<?php


namespace Packages\ShaunSocial\PaidContent\Wallet\Commission;

use Packages\ShaunSocial\Wallet\Wallet\Payment\WalletPaymentTypeBase;

class PaidContentTransactionTypeBuyPost extends WalletPaymentTypeBase
{
    public function getDescription($transaction)
    {
        $subject = $transaction->getSubject();
        $user = $subject->getPostOwner();
        $user = getUserIncludeDelete($user);

        return __("Receive referral commission from :1's exclusive paid content", ['1' => $user->getTitle()]);
    }
}