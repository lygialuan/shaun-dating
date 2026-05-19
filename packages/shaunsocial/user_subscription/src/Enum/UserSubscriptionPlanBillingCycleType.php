<?php


namespace Packages\ShaunSocial\UserSubscription\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum UserSubscriptionPlanBillingCycleType: string
{
    use EnumToArray;
    
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public static function getAll(): array
    {
        return [
            'day' => __('Day(s)'),
            'week' => __('Week(s)'),
            'month' => __('Month(s)'),
            'year' => __('Year(s)')
        ];
    }
}
