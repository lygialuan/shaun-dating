<?php


namespace Packages\ShaunSocial\PaidContent\Enum;

enum UserPostPaidOrderStatus: string {
    case INIT = 'init';
    case DONE = 'done';
}