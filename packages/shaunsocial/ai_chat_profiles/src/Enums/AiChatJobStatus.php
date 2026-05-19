<?php

namespace Packages\ShaunSocial\AiChatProfiles\Enums;

enum AiChatJobStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case SENT = 'sent';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case OVERRIDDEN = 'overridden';
}
