<?php


namespace Packages\ShaunSocial\Core\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum InviteType: string {
    use EnumToArray;
    
    case INVITE = 'invite';
    case REFERRAL = 'referral';
}