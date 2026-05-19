<?php


namespace Packages\ShaunSocial\Story\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;

class StoryView extends Model
{
    use HasCachePagination;

    public function getListCachePagination()
    {
        return [
            'story_viewer_'.$this->story_item_id
        ];
    }

    protected $fillable = [
        'user_id',
        'story_item_id',
        'story_id'
    ];

    public static function getCacheStoryViewKey($userId, $storyItemId)
    {
        return 'story_view_'.$userId.'_'.$storyItemId;
    }

    public static function getView($userId, $storyItemId)
    {
        return Cache::remember(self::getCacheStoryViewKey($userId, $storyItemId), config('shaun_core.cache.time.model_query'), function () use ($userId, $storyItemId) {
            $storyView = self::where('user_id', $userId)->where('story_item_id', $storyItemId)->first();

            return is_null($storyView) ? false : $storyView;
        });
    }

    public static function getCacheStoryLastViewKey($userId, $storId)
    {
        return 'story_view_last_'.$userId.'_'.$storId;
    }

    public static function getLastSeen($userId, $storId)
    {
        return Cache::remember(self::getCacheStoryLastViewKey($userId, $storId), config('shaun_core.cache.time.model_query'), function () use ($userId, $storId) {
            $last = self::where('user_id', $userId)->where('story_id', $storId)->orderBy('id', 'DESC')->limit(1)->first();
            return $last ? $last->story_item_id : 0;
        });
    }

    public static function getCacheStoryCountView($storyItemId)
    {
        return 'story_view_count_'.$storyItemId;
    }
    

    public static function getCount($userId, $storyItemId)
    {
        return Cache::remember(self::getCacheStoryCountView($storyItemId), config('shaun_core.cache.time.model_query'), function () use ($userId, $storyItemId) {
            return self::where('user_id', '!=', $userId)->where('story_item_id', $storyItemId)->count();
        });
    }
    
    public function clearCache()
    {
        Cache::forget(self::getCacheStoryLastViewKey($this->user_id, $this->story_id));
        Cache::forget(self::getCacheStoryViewKey($this->user_id, $this->story_item_id));
        Cache::forget(self::getCacheStoryCountView($this->story_item_id));
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($storyView) {
            $storyView->clearCache();
        });
    }
}
