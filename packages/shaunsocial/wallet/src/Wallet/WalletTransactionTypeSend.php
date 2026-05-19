<?php

namespace Packages\ShaunSocial\Wallet\Wallet;

class WalletTransactionTypeSend extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $user = $transaction->getSubject();
        if (! $user) {
            $user = getDeleteUser();
        }

        $params = $transaction->getParams();
        $type = $params['type'] ?? 'fund';

        $messages = [
            'gift' => [
                'send' => __('Send a gift to'),
                'receive' => __('Receive a gift from'),
            ],
            'fund' => [
                'send' => __('Send funds to'),
                'receive' => __('Receive funds from'),
            ],
        ];

        $key = $transaction->amount < 0 ? 'send' : 'receive';

        return ($messages[$type][$key] ?? $messages['fund'][$key]) . ' ' . $user->getName();
    }

    public function getName()
    {
        return __('Send and receive funds');
    }

    public function getGross($transaction)
    {
        $params = $transaction->getParams();
        $fee = $params['fee'] ?? 0;

        if ($transaction->amount >= 0) {
            return formatNumber($transaction->amount + $fee);
        }

        return formatNumber($transaction->amount);
    }

    public function getNet($transaction)
    {
        return formatNumber($transaction->amount);
    }

    public function getFee($transaction)
    {
        $params = $transaction->getParams();
        $fee = $params['fee'] ?? 0;

        if ($transaction->amount >= 0) {
            return formatNumber($fee);
        }

        return 0;
    }
}
