<?php

namespace Packages\ShaunSocial\UserSubscription\Enum;

enum UserSubscriptionOrderStatus: string {
    case RUNNING = 'init';
    case PROCESS = 'process';
    case CANCEL = 'cancel';
    case DONE = 'done';
    case REFUND = 'refund';
}