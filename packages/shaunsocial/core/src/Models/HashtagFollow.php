<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;

class HashtagFollow extends Model
{
    use HasCacheQueryFields, HasCachePagination;

    public function getHashtag()
    {
        return Hashtag::findByField('id', $this->hashtag_id);
    }

    public function getListCachePagination()
    {
        return [
            'hashtag_'.$this->user_id
        ];
    }

    protected $cacheQueryFields = [
        'user_id',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'hashtag_id'
    ];

    public static function getFollow($userId, $hashtag)
    {
        return Cache::remember(self::getCacheFollowKey($userId, $hashtag), config('shaun_core.cache.time.model_query'), function () use ($userId, $hashtag) {
            $follow = self::where('user_id', $userId)->where('name', $hashtag)->first();

            return is_null($follow) ? false : $follow;
        });
    }

    public static function getCacheFollowKey($userId, $hashtag)
    {
        return 'hashtag_follow_'.$userId.'_'.$hashtag;
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheFollowKey($this->user_id, $this->name));
        Cache::forget('hashtag_trending_'.$this->user_id);
        Cache::forget('hashtag_suggest_'.$this->user_id);
        Cache::forget('user_trending_'.$this->user_id);
        Cache::forget('user_suggest_'.$this->user_id);
        Cache::forget('user_hashtag_suggest_'.$this->user_id);
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($follow) {
            $hashtag = Hashtag::firstOrCreate([
                'name' => $follow->name,
            ]);

            $follow->hashtag_id = $hashtag->id;
        });
        
        static::created(function ($follow) {
            User::findByField('id', $follow->user_id)->increment('hashtag_follow_count');

            //delete suggest
            UserHashtagSuggest::where('user_id', $follow->user_id)->where('hashtag_id', $follow->hashtag_id)->delete();
            
            $follow->clearCache();
        });

        static::deleted(function ($follow) {
            User::findByField('id', $follow->user_id)->decrement('hashtag_follow_count');

            $follow->clearCache();
        });
    }
}
