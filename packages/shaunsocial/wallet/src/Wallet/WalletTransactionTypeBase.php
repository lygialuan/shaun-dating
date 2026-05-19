<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

abstract class WalletTransactionTypeBase
{
    abstract public function getDescription($transaction);
    abstract public function getName();

    public function getGross($transaction)
    {
        return formatNumber($transaction->amount);
    }

    public function getFee($transaction)
    {
        return '0';
    }

    public function getNet($transaction)
    {
        return formatNumber($transaction->amount);
    }

    public function getExtra($transaction)
    {
        return [];
    }
}
