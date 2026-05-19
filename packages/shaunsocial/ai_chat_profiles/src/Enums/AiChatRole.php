<?php

namespace Packages\ShaunSocial\AiChatProfiles\Enums;

enum AiChatRole: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
}
