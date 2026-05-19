<?php


namespace Packages\ShaunSocial\Story\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCacheShortStory
{
    public static function bootHasCacheShortStory()
    {
        static::created(function ($model) {
            $model->clearCacheShortStory();
        });

        static::deleted(function ($model) {
            $model->clearCacheShortStory();
        });
    }

    public function clearCacheShortStory()
    {
        for ($i = 1; $i <= config('shaun_core.cache.short.page_clear_cache'); $i++) {
            Cache::forget('story_' . $this->user_id . '_' . $i);
        }
    }
}
