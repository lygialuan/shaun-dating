<?php

namespace Packages\ShaunSocial\Group\Enum;

enum GroupStatus: string {
    case ACTIVE = 'active';
    case HIDDEN = 'hidden';
    case PENDING = 'pending';
    case DISABLE = 'disable';

    public static function getAll(): array
    {
        return [
            'active' => __('Active'),
            'hidden' => __('Hidden'),
            'pending' => __('Pending'),
            'disable' => __('Disable')
        ];
    } 
}