<?php


namespace Packages\ShaunSocial\Core\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum PostPaidType: string {
    use EnumToArray;
    
    case SUBSCRIBER = 'subscriber';
    case PAYPERVIEW = 'pay_per_view';
}