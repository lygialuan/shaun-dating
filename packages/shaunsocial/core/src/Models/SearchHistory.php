<?php

namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class SearchHistory extends Model
{
    use HasCacheQueryFields, HasUser;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'query',
        'user_id',
    ];

    public static function getByUser($userId) 
    {
        return Cache::remember(self::getByUserCacheName($userId), config('shaun_core.cache.time.model_query'), function () use ($userId) {
            return self::where('user_id', $userId)->orderBy('id', 'DESC')->limit(config('shaun_core.core.suggest_search_limit'))->get();
        });
    }

    public static function getCountByUser($userId) 
    {
        return Cache::remember(self::getCountByUserCacheName($userId), config('shaun_core.cache.time.model_query'), function () use ($userId) {
            return self::where('user_id', $userId)->count();
        });
    }

    public static function getByUserCacheName($userId) 
    {
        return "search_history_{$userId}";
    }

    public static function getCountByUserCacheName($userId) 
    {
        return "search_history_count_{$userId}";
    }

    public static function clearSearchHistoryCache($userId) 
    {
        Cache::forget(self::getByUserCacheName($userId));
        Cache::forget(self::getCountByUserCacheName($userId));
    }

    public static function booted() 
    {
        parent::booted();

        self::created(function ($searchHistory) {
            self::clearSearchHistoryCache($searchHistory->user_id);
        });
        
        self::deleted(function ($searchHistory) {
            self::clearSearchHistoryCache($searchHistory->user_id);
        });
    }
}
