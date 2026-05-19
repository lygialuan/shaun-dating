<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageReviewResource;

class UserPageReview extends Model
{
    use HasCacheQueryFields, IsSubject, HasCachePagination, HasUser;
    
    protected $cacheQueryFields = [
        'user_page_id'
    ];

    protected $fillable = [
        'user_id',
        'user_page_id',
        'is_recommend',
        'post_id'
    ];

    protected $casts = [
        'is_recommend' => 'boolean',
    ];

    public function getListCachePagination()
    {
        return [
            'user_page_review_'.$this->user_page_id,
        ];
    }

    public function getPage()
    {
        return User::findByField('id', $this->user_page_id);
    }

    public static function getResourceClass()
    {
        return UserPageReviewResource::class;
    }

    public static function getCacheReviewKey($userId, $userPageId)
    {
        return 'user_page_review_'.$userId.'_'.$userPageId;
    }

    public static function getReview($userId, $userPageId)
    {
        return Cache::remember(self::getCacheReviewKey($userId, $userPageId), config('shaun_core.cache.time.model_query'), function () use ($userId, $userPageId) {
            $review = self::where('user_id', $userId)->where('user_page_id', $userPageId)->first();

            return is_null($review) ? false : $review;
        });
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheReviewKey($this->user_id, $this->user_page_id));
    }

    public static function getScore($review, $delete = false)
    {
        $countRecommend = self::where('user_page_id', $review->user_page_id)->where('id', '!=', $review->id)->where('is_recommend', true)->count();
        $countNoRecommend = self::where('user_page_id', $review->user_page_id)->where('id', '!=', $review->id)->where('is_recommend', false)->count();

        if (! $delete) {
            if ($review->is_recommend) {
                $countRecommend++;
            } else {
                $countNoRecommend++;
            }
        }
        $total = $countNoRecommend + $countRecommend;
        $score = 0;
        if ($total > 0) {
            $score = (($countNoRecommend*config('shaun_user_page.score_min') + $countRecommend*config('shaun_user_page.score_max')) / $total);
        }
        return ['score' => $score, 'total' => $total];
    }

    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($review) {
            $review->clearCache();
            $pageInfo = UserPageInfo::findByField('user_page_id', $review->user_page_id); 
            if ($pageInfo) { 
                $result = self::getScore($review);
                $pageInfo->update([
                    'review_score' => $result['score'],
                    'review_count' => $result['total']
                ]);
            }
        });

        static::deleting(function ($review) {
            $review->clearCache();
            $pageInfo = UserPageInfo::findByField('user_page_id', $review->user_page_id); 
            if ($pageInfo) {
                $result = self::getScore($review, true);
                $pageInfo->update([
                    'review_score' => $result['score'],
                    'review_count' => $result['total']
                ]);
            }
        });
    }
}
