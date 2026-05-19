<?php


namespace Packages\ShaunSocial\PaidContent\Wallet\Payment;

use Packages\ShaunSocial\Wallet\Wallet\WalletTransactionTypeBase;

class PaidContentTransactionTypeRootBuyPostFee extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $subject = $transaction->getSubject();
        $user = $subject->getUser(true);

        return __('Receive funds from :1 for paid content fee', ['1' => $user->getTitle()]);
    }

    public function getName()
    {
        return __('Paid post fee');
    }
}
