<?php


namespace Packages\ShaunSocial\Wallet\Traits;

trait HasItemFromSite
{
    public function getWalletTypeExtra()
    {
        return '';
    }

    public function getWalletTypeExtraRefund()
    {
        return '';
    }

    public function getRefundAmount()
    {
        return 0;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}
