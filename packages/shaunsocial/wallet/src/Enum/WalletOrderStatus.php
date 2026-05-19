<?php

namespace Packages\ShaunSocial\Wallet\Enum;

enum WalletOrderStatus: string {
    case RUNNING = 'init';
    case PROCESS = 'process';
    case CANCEL = 'cancel';
    case DONE = 'done';
    case REFUND = 'refund';
}