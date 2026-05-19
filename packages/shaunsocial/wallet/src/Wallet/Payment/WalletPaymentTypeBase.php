<?php


namespace Packages\ShaunSocial\Wallet\Wallet\Payment;

abstract class WalletPaymentTypeBase
{
    abstract public function getDescription($transaction);
}
