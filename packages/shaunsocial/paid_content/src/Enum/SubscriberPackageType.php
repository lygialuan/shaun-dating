<?php


namespace Packages\ShaunSocial\PaidContent\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum SubscriberPackageType: string {
    use EnumToArray;
    
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case BIANNUAL = 'biannual';
    case ANNUAL = 'annual';

    public static function getAll(): array
    {
       return [
        'monthly' => __('Monthly'),
        'quarterly' => __('Quarterly'),
        'biannual' => __('Biannual'),
        'annual' => __('Annual'),
       ];
    }
}