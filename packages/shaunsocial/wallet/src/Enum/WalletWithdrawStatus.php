<?php

namespace Packages\ShaunSocial\Wallet\Enum;

enum WalletWithdrawStatus: string {
    case INIT = 'init';
    case DONE = 'done';
    case REJECT = 'reject';

    public static function getAll(): array
    {
       return [
        'init' => __('Pending'),
        'done' => __('Sent'),
        'reject' => __('Rejected')
       ];
    }
}