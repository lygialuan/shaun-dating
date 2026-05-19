<?php

namespace Packages\ShaunSocial\Dating\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Illuminate\Support\Facades\Cache;

class DatingSwipeMatch extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'user_one_id',
        'user_two_id',
    ];

    public static function checkMatched($userOneId, $userTwoId)
    {
        $min = min($userOneId, $userTwoId);
        $max = max($userOneId, $userTwoId);

        return Cache::remember(
            "check_matched_{$min}_{$max}",
            config('shaun_core.cache.time.model_query'),
            fn () => self::where(function ($q) use ($min, $max) {
                $q->where('user_one_id', $min)
                ->where('user_two_id', $max);
            })->orWhere(function ($q) use ($min, $max) {
                $q->where('user_one_id', $max)
                ->where('user_two_id', $min);
            })->exists()
        );
    }

    public function clearCache()
    {
        Cache::forget("check_matched_{$this->user_one_id}_{$this->user_two_id}");
        Cache::forget("check_matched_{$this->user_two_id}_{$this->user_one_id}");
    }

    protected static function booted()
    {
        static::saved(function ($swipe) {
            $swipe->clearCache();
        });
    }
}
