<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

class WalletTransactionTypeWithdraw extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $withdraw = $transaction->getSubject();
        if ($transaction->amount > 0) {
            return $withdraw->getPaymentMethod(). ' - '.__('Rejected');
        }
        return $withdraw->getPaymentMethod(). ' ('.$withdraw->getExchangeInfo(). ')';
    }

    public function getGross($transaction)
    { 
        if ($transaction->amount > 0) {
            return parent::getGross(($transaction));
        }
        $withdraw = $transaction->getSubject();
        return $withdraw->getGross();
    }

    public function getFee($transaction)
    {
        if ($transaction->amount > 0) {
            return parent::getFee(($transaction));
        }
        $withdraw = $transaction->getSubject();
        return $withdraw->getFee();
    }

    public function getNet($transaction)
    {
        if ($transaction->amount > 0) {
            return parent::getNet(($transaction));
        }
        $withdraw = $transaction->getSubject();
        return $withdraw->getNet();
    }

    public function getName()
    {
        return __('Transfer funds');
    }
}
