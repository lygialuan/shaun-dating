<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

use Packages\ShaunSocial\Wallet\Models\WalletTransactionSubType;

class WalletTransactionTypeCommission extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $class = WalletTransactionSubType::getClassByType('commission', $transaction->type_extra);
        return app($class)->getDescription($transaction);
    }

    public function getName()
    {
        return __('Commission earned from referral');
    }
}
