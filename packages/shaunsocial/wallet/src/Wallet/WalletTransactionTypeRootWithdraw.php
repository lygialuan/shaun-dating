<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

class WalletTransactionTypeRootWithdraw extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $subject = $transaction->getSubject();
        $user = getDeleteUser();
        if ($subject) {
            $userObject = $subject->getUser();
            if ($userObject) {
                $user = $userObject;
            }
        }
        return __('Cast out funds to'). ' '. $user->getName();
    }

    public function getName()
    {
        return __('Transfer funds');
    }
}
