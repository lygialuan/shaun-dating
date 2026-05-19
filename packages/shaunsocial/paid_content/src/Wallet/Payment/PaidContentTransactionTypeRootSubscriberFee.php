<?php


namespace Packages\ShaunSocial\PaidContent\Wallet\Payment;

use Packages\ShaunSocial\Wallet\Wallet\WalletTransactionTypeBase;

class PaidContentTransactionTypeRootSubscriberFee extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $subject = $transaction->getSubject();
        $user = $subject->getUser(true);

        return __('Receive funds from :1 for exclusive content subscription fee', ['1' => $user->getTitle()]);
    }

    public function getName()
    {
        return __('User subscriber fee');
    }
}
