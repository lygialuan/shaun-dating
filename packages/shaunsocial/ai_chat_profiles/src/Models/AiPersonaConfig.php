<?php

namespace Packages\ShaunSocial\AiChatProfiles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatIntent;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatTone;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiMessageLength;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class AiPersonaConfig extends Model
{
    use HasCacheQueryFields;

    protected $table = 'ai_persona_configs';

    protected $cacheQueryFields = [
        'id',
        'profile_id'
    ];

    protected $fillable = [
        'profile_id',
        'enabled',
        'tone',
        'intent',
        'trait_playfulness',
        'trait_warmth',
        'trait_assertiveness',
        'message_length',
        'max_messages_per_day',
        'reply_delay_min_sec',
        'reply_delay_max_sec',
        'custom_system_prompt',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'tone' => AiChatTone::class,
        'intent' => AiChatIntent::class,
        'trait_playfulness' => 'integer',
        'trait_warmth' => 'integer',
        'trait_assertiveness' => 'integer',
        'message_length' => AiMessageLength::class,
        'max_messages_per_day' => 'integer',
        'reply_delay_min_sec' => 'integer',
        'reply_delay_max_sec' => 'integer',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profile_id');
    }
}
