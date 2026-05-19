<?php

namespace Packages\ShaunSocial\AiChatProfiles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Models\User;

class AiMessageLog extends Model
{
    protected $table = 'ai_message_log';

    protected $fillable = [
        'room_id',
        'profile_id',
        'job_id',
        'chat_message_id',
        'provider',
        'model',
        'tokens_prompt',
        'tokens_completion',
        'latency_ms',
        'flagged',
        'flag_reason',
        'prompt_preview',
        'reply_preview',
    ];

    protected $casts = [
        'tokens_prompt' => 'integer',
        'tokens_completion' => 'integer',
        'latency_ms' => 'integer',
        'flagged' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profile_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(AiChatJob::class, 'job_id');
    }

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }
}
