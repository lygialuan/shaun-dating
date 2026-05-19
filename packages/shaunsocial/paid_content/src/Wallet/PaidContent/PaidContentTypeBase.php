<?php


namespace Packages\ShaunSocial\PaidContent\Wallet\PaidContent;

abstract class PaidContentTypeBase
{
    abstract public function getDescription($transaction);

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
}
