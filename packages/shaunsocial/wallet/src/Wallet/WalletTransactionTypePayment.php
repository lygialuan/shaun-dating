<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

use Packages\ShaunSocial\Wallet\Models\WalletTransactionSubType;

class WalletTransactionTypePayment extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $class = WalletTransactionSubType::getClassByType('payment', $transaction->type_extra);
        return app($class)->getDescription($transaction);
    }

    public function getName()
    {
        return __('Send and receive funds with platform');
    }
}
