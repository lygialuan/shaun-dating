<?php

namespace Packages\ShaunSocial\AiChatProfiles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatJobStatus;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Models\User;

class AiChatJob extends Model
{
    protected $table = 'ai_chat_jobs';

    protected $fillable = [
        'room_id',
        'profile_id',
        'sender_id',
        'trigger_message_id',
        'reply_message_id',
        'status',
        'scheduled_at',
        'started_at',
        'finished_at',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'status' => AiChatJobStatus::class,
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profile_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function triggerMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'trigger_message_id');
    }

    public function replyMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_message_id');
    }
}
