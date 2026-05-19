<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Story\Traits\HasCacheShortStory;
use Packages\ShaunSocial\UserPage\Models\UserPageFollowReport;
use Packages\ShaunSocial\UserPage\Models\UserPageStatistic;

class UserFollow extends Model
{
    use HasCachePagination, HasCacheQueryFields, HasCacheShortStory;

    protected $fillable = [
        'user_id',
        'follower_id',
        'user_is_page',
        'follower_is_page',
        'enable_notify'
    ];

    protected $casts = [
        'enable_notify' => 'boolean',
        'user_is_page' => 'boolean',
        'follower_is_page' => 'boolean',
    ];

    protected $cacheQueryFields = [
        'user_id',
    ];

    public function getListCachePagination()
    {
        return [
            'following_'.$this->user_id,
            'follower_'.$this->follower_id,
            'following_user_'.$this->user_id,
            'follower_user_'.$this->follower_id,
            'following_page_'.$this->user_id,
            'follower_page_'.$this->follower_id,
        ];
    }

    public static function getFollow($userId, $followerId)
    {
        return Cache::remember(self::getCacheFollowerKey($userId, $followerId), config('shaun_core.cache.time.model_query'), function () use ($userId, $followerId) {
            $follow = self::where('user_id', $userId)->where('follower_id', $followerId)->first();

            return is_null($follow) ? false : $follow;
        });
    }

    public static function getCacheFollowerKey($userId, $followerId)
    {
        return 'follow_'.$userId.'_'.$followerId;
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheFollowerKey($this->user_id, $this->follower_id));
        Cache::forget('user_trending_'.$this->user_id);
        Cache::forget('user_trending_'.$this->follower_id);
        Cache::forget('user_suggest_'.$this->user_id);
        Cache::forget('user_teacher_followed_'.$this->user_id);
        Cache::forget('user_suggest_popular_'.$this->user_id);
    }
    
    public static function booted()
    {
        parent::booted();

        static::created(function ($follow) {
            $user = User::findByField('id', $follow->user_id);
            if ($user) {
                $user->increment('following_count');
            }
            
            $follower = User::findByField('id', $follow->follower_id);
            if ($follower) {
                $follower->increment('follower_count');
            }

            if ($user && $follower) {
                $follower->addPageStatistic('follow', $user, null, false);
            }

            if ($user && $follower && $follower->isPage()) {
                UserPageFollowReport::create([
                    'user_id' => $user->id,
                    'user_page_id' => $follower->id,
                    'birthday' => $user->getBirthdayYear(),
                    'gender_id' => $user->gender_id
                ]);
            }
            $follow->clearCache();
        });

        static::updated(function ($follow) {
            Cache::forget(self::getCacheFollowerKey($follow->user_id, $follow->follower_id));
        });

        static::deleted(function ($follow) {
            $user = User::findByField('id', $follow->user_id);
            if ($user) {
                $user->decrement('following_count');
            }
            
            $user = User::findByField('id', $follow->follower_id);
            if ($user) {
                $user->decrement('follower_count');
            }

            if ($follow->follower_is_page) {
                UserPageStatistic::remove($follow->follower_id, 'follow', $follow->user_id);

                $report = UserPageFollowReport::getByUserIdAndPageId($follow->user_id, $follow->follower_id);
                if ($report) {
                    $report->delete();
                }
            }

            $follow->clearCache();
        });
    }
}
