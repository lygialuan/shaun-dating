<?php

namespace Packages\ShaunSocial\UserPage\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum UserPageFeaturePackageType: string {
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