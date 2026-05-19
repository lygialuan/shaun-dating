<?php


namespace Packages\ShaunSocial\Core\Enum;

enum UserVerifyStatus: string {
    case NONE = 'none';
    case SENT = 'sent';
    case OK = 'ok';
}