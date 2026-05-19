<?php


namespace Packages\ShaunSocial\Core\Enum;

enum PhotoVerifyItemStatus: string {
    case PENDING = 'pending';
    case REJECT = 'reject';
    case APPROVE = 'approve';
}