<?php


namespace Packages\ShaunSocial\Advertising\Enum;

enum AdvertisingStatus: string
{
    case ACTIVE = 'active';
    case STOP = 'stop';
    case DONE = 'done';

    public static function getAll(): array
    {
        return [
            'active' => __('Active'),
            'stop' => __('Stopped'),
            'done' => __('Completed')
        ];
    }
}
