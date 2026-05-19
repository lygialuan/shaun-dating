<?php

namespace Packages\ShaunSocial\Chatbot\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;

class ChatbotHistory extends Model
{
    use HasCachePagination, Prunable;

    protected $fillable = [
        'user_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getListCachePagination()
    {
        return [
            'chat_bot_history_'.$this->user_id,
        ];
    }

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
