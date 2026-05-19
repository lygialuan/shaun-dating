<?php


namespace Packages\ShaunSocial\UserSubscription\Enum;

enum UserSubscriptionStatus: string {
    case INIT = 'init';
    case ACTIVE = 'active';
    case CANCEL = 'cancel';
    case STOP = 'stop';

    public static function getAll(): array
    {
       return [
        'init' => __('Init'),
        'active' => __('Active'),
        'cancel' => __('Cancelled'),
        'stop' => __('Stopped')
       ];
    }
}