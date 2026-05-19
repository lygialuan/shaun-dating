<?php


namespace Packages\ShaunSocial\Core\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum SubscriptionBillingCycleType: string {
    use EnumToArray;
    
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
}