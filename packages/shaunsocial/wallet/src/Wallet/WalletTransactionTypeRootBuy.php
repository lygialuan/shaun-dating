<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

use Packages\ShaunSocial\Core\Models\User;

class WalletTransactionTypeRootBuy extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $subject = $transaction->getSubject();
        $user = getDeleteUser();
        if ($subject) {
            if ($subject instanceof User) {
                $user = $subject;
            } else {
                $userObject = $subject->getUser();
                if ($userObject) {
                    $user = $userObject;
                }
            }
        }
        return __('Deposit funds from'). ' '. $user->getName();
    }

    public function getName()
    {
        return __('Add funds');
    }
}
