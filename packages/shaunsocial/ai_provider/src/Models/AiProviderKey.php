<?php

namespace Packages\ShaunSocial\AiProvider\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class AiProviderKey extends Model
{
    use HasTranslations;
    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ai_provider_id',
        'name',
        'description',
        'config',
        'is_active',
        'status',
        'failure_count',
        'last_error_message',
        'last_error_at',
    ];

    /**
     * Attributes that support translation.
     *
     * @var array<int, string>
     */
    protected array $translatable = [
        'description',
    ];

    /**
     * Attribute casting definitions.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'failure_count' => 'integer',
        'last_error_at' => 'datetime',
    ];

    /**
     * Relation: API key belongs to a provider.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    /**
     * Determine if key is usable.
     */
    public function isUsable(): bool
    {
        return $this->is_active && $this->status === 'healthy';
    }

    /**
     * Determine if the given key is currently referenced by the application.
     */
    public static function isInUse(int $keyId): bool
    {
        $settingKeys = [
            'ai_features.chatbot_provider_key_id',
            'ai_features.text_provider_key_id',
            'ai_features.image_provider_key_id',
            'ai_features.video_provider_key_id',
            'ai_features.auto_delete_provider_key_id',
            'ai_chat_profiles.chat_provider_key_id',
            // add more setting
        ];

        foreach ($settingKeys as $settingKey) {
            if ((int) setting($settingKey, 0) === $keyId) {
                return true;
            }
        }

        return false;
    }
}
