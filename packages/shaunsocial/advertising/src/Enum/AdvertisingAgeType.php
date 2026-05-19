<?php


namespace Packages\ShaunSocial\Advertising\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum AdvertisingAgeType: string {
    use EnumToArray;
    
    case ANY = 'any';
    case RANGE = 'range';
}
