<?php

namespace Packages\ShaunSocial\AiChatProfiles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\ShaunSocial\AiChatProfiles\Models\AiChatJob;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class AiChatSuggestion extends Model
{
    use HasCacheQueryFields;

    protected $table = 'ai_chat_suggestions';

    protected $cacheQueryFields = [
        'room_id',
        'user_id',
    ];

    protected $fillable = [
        'room_id',
        'profile_id',
        'user_id',
        'job_id',
        'chat_message_id',
        'suggestion_text',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(AiChatJob::class);
    }

    public function triggerMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }
}

