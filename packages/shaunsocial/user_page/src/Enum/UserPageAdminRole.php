<?php

namespace Packages\ShaunSocial\UserPage\Enum;

enum UserPageAdminRole: string {
    case ADMIN = 'admin';
    case OWNER = 'owner';
}