<?php

namespace Packages\ShaunSocial\Dating\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Models\User;

class DatingSwipe extends Model
{
    use HasCacheQueryFields, HasCachePagination;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'user_id',
        'target_user_id',
        'action',
    ];

    public static function checkSwipes($userId, $targetUserId)
    {
        return Cache::remember(self::getCacheCheckSwipeKey($userId, $targetUserId), config('shaun_core.cache.time.model_query'), function () use ($userId, $targetUserId) {
            return self::where('user_id', $userId)->where('target_user_id', $targetUserId)->where('action', '<>', 'viewed')->exists();
        });
    }

    public function getListCachePagination()
    {
        return [
            "get_dating_swipe_i_liked_{$this->user_id}",
            "get_dating_swipe_viewed_{$this->user_id}",
            "get_dating_swipe_liked_me_{$this->user_id}",
            "get_dating_swipe_viewed_me_{$this->user_id}",

            "get_dating_swipe_i_liked_{$this->target_user_id}",
            "get_dating_swipe_viewed_{$this->target_user_id}",
            "get_dating_swipe_liked_me_{$this->target_user_id}",
            "get_dating_swipe_viewed_me_{$this->target_user_id}",
        ];
    }

    public function getUser($id)
    {
        $user = User::findByField('id', $id);
        return $user ?? getDeleteUser();
    }

    public static function getCacheCheckSwipeKey($userId, $targetUserId)
    {
        return 'swipe_check_'.$userId.'_'.$targetUserId;
    }

    public static function convertDislikeToViewed($userId, $targetUserId)
    {
        // UserA swipe left on UserB : dislike
        // UserB swipe right on UserA : like
        // UserA can see UserB and swipe again
        return self::where('user_id', $userId)->where('target_user_id', $targetUserId)->where('action', 'dislike')->update(['action' => 'viewed']);
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheCheckSwipeKey($this->user_id, $this->target_user_id));
        Cache::forget(self::getCacheCheckSwipeKey($this->target_user_id, $this->user_id));
    }

    public static function booted()
    {
        parent::booted();

        static::saved(function ($swipe) {
            $swipe->clearCache();
        });

        static::deleted(function ($swipe) {
            $swipe->clearCache();
        });
    }
}
