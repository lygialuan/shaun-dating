<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;

class UserActionLog extends Model
{
    use Prunable;
    
    protected $fillable = [
        'user_id',
        'type'
    ];

    public static function getCount($userId, $type, $day = 1)
    {
        return Cache::remember(self::getKeyCache($userId, $type), config('shaun_core.cache.time.model_query'), function () use ($userId, $type, $day) {
            return self::where('user_id', $userId)->where('type', $type)->where('created_at', '>', now()->subDays($day))->count();
        });
    }

    public static function getKeyCache($userId, $type)
    {
        return 'user_action_log_'.$userId.'_'.$type;
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($log) {
            Cache::forget(self::getKeyCache($log->user_id, $log->type));
        });
    }
    
    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
