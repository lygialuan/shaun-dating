<?php


namespace Packages\ShaunSocial\Core\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum CommentPrivacy: string {
    use EnumToArray;
    
    case EVERYONE = 'everyone';
    case FOLLOWING = 'following';
    case VERIFIED = 'verified';
    case MENTIONED = 'mentioned';
}