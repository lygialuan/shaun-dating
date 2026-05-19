<?php


namespace Packages\ShaunSocial\PaidContent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SubscriberTrial extends Model
{
    protected $fillable = [
        'user_id',
        'subscriber_id'
    ];

    static function getSubscriberTrial($userId, $subscriberId)
    {
        return Cache::remember(self::getKeyCache($userId, $subscriberId), config('shaun_core.cache.time.model_query'), function () use ($userId, $subscriberId) {
            $trial = self::where('user_id', $userId)->where('subscriber_id', $subscriberId)->first();

            return is_null($trial) ? false : $trial;
        });
    }

    static function getKeyCache($userId, $subscriberId)
    {
        return 'subscriber_trials_'.$userId.'_'.$subscriberId;
    }

    public function clearCache()
    {
        Cache::forget(self::getKeyCache($this->user_id, $this->subscriber_id));
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($trial) {
            $trial->clearCache();
        });

        static::saved(function ($trial) {
            $trial->clearCache();
        });
    }
}
