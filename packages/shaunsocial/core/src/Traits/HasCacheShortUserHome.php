<?php


namespace Packages\ShaunSocial\Core\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCacheShortUserHome
{
    public static function bootHasCacheShortUserHome()
    {
        static::created(function ($model) {
            $model->clearCacheShortUser();
        });

        static::updating(function ($model) {
            $model->clearCacheShortUser();
        });

        static::deleted(function ($model) {
            $model->clearCacheShortUser();
        });
    }

    public function clearCacheShortUser()
    {
        for ($i = 1; $i <= config('shaun_core.cache.short.page_clear_cache'); $i++) {
            Cache::forget('post_user_home_' . $this->user_id . '_' . $i);
        }
    }
}
