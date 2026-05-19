<?php


namespace Packages\ShaunSocial\Core\Enum;

enum UserListMessageType: string {
    case FOLLOWER = 'follower';
    case FOLLOWING = 'following';
    case SUBSCRIBER = 'subscriber';
    case LIST = 'list';
    case SPECIFIC = 'specific';
}