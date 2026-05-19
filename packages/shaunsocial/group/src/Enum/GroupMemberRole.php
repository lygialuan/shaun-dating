<?php

namespace Packages\ShaunSocial\Group\Enum;

enum GroupMemberRole: string {
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case MEMBER = 'member';
}