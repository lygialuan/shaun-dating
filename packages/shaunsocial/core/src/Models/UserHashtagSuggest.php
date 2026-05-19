<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserHashtagSuggest extends Model
{
    use Prunable;

    protected $fillable = [
        'hashtag_id',
        'user_id',
        'name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getCacheUserKey($userId)
    {
        return 'user_hashtag_suggest_user_'. $userId;
    }

    static public function getHashtagForUser($userId)
    {
        return Cache::remember(self::getCacheUserKey($userId), config('shaun_core.cache.time.model_query'), function () use ($userId) {
            return self::where('user_id', $userId)->orderBy('count')->groupBy('hashtag_id')->selectRaw('hashtag_id, count(*) as count')->limit(config('shaun_core.core.hashtag_suggest_relative_limit'))->get();
        });
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheUserKey($this->user_id));
    }

    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(setting('feature.user_suggest_hastag_delete_day')))->limit(setting('feature.item_per_page'));
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($suggest) {
            $suggest->clearCache();
        });

        static::deleted(function ($suggest) {
            $suggest->clearCache();
        });
    }
}
