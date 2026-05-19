<?php


namespace Packages\ShaunSocial\PaidContent\Wallet;

use Packages\ShaunSocial\Wallet\Models\WalletTransactionSubType;
use Packages\ShaunSocial\Wallet\Wallet\WalletTransactionTypeBase;

class WalletTransactionTypePaidContent extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        return WalletTransactionSubType::getObjectClassByType('paid_content', $transaction->type_extra)->getDescription($transaction);
    }

    public function getName()
    {
        return __('Send and receive funds for paid content');
    }

    public function getGross($transaction)
    {
        return WalletTransactionSubType::getObjectClassByType('paid_content', $transaction->type_extra)->getGross($transaction);
    }

    public function getFee($transaction)
    {
        return WalletTransactionSubType::getObjectClassByType('paid_content', $transaction->type_extra)->getFee($transaction);
    }

    public function getNet($transaction)
    {
        return WalletTransactionSubType::getObjectClassByType('paid_content', $transaction->type_extra)->getNet($transaction);
    }

    public function getExtra($transaction)
    {
        return [];
    }
}
