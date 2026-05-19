<?php

namespace Packages\ShaunSocial\Wallet\Enum;

enum WalletNotifyBalanceType: string {
    case ADD = 'add';
    case REDUCE = 'reduce';
}