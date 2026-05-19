<?php


namespace Packages\ShaunSocial\Core\Enum;

enum SubscriptionTransactionStatus: string {
    case RUNNING = 'init';
    case PROCESS = 'process';
    case CANCEL = 'cancel';
    case PAID = 'paid';
    case REFUND = 'refund';

    public static function getAll(): array
    {
       return [
        'init' => __('Init'),
        'process' => __('Process'),
        'cancel' => __('Cancelled'),
        'paid' => __('Paid'),
        'refund' => __('Refunded')
       ];
    }
}