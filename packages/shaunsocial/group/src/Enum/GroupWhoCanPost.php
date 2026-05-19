<?php

namespace Packages\ShaunSocial\Group\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum GroupWhoCanPost: string {
    use EnumToArray;
    
    case MEMBER = 'member';
    case ADMIN = 'admin';

    public static function getAll(): array
    {
        return [
            'member' => __('All members'),
            'admin' => __('Admins and moderators')
        ];
    }
}