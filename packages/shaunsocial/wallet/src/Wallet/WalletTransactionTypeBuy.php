<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

use Packages\ShaunSocial\Wallet\Enum\WalletOrderStatus;

class WalletTransactionTypeBuy extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        switch ($transaction->type_extra) {
            case 'mass_fund':
                return __('Add funds to your eWallet');
                break;
        }
        $subject = $transaction->getSubject();
        if ($subject->status != WalletOrderStatus::DONE) {
            return __('Add funds to your eWallet (pending)');
        }
        return __('Add funds to your eWallet');
    }

    public function getName()
    {
        return __('Add funds');
    }
}
