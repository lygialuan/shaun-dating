<?php


namespace Packages\ShaunSocial\PaidContent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\Subscription;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class UserSubscriber extends Model
{
    use HasCacheQueryFields, Prunable, HasCachePagination;

    protected $cacheQueryFields = [
        'id',
        'subscription_id'
    ];

    protected $fillable = [
        'user_id',
        'subscriber_id',
        'subscription_id',
        'is_active'
    ];

    public function getListCachePagination()
    {
        return [
            'user_subscriber_'.$this->user_id,
        ];
    }

    public function getSubscription()
    {
        return Subscription::findByField('id', $this->subscription_id);
    }

    public function getSubscriber()
    {
        return User::findByField('id', $this->subscriber_id);
    }

    public static function getUserSubscriber($userId, $subscriberId)
    {
        if (! $userId) {
            return false;
        }

        return Cache::remember(self::getKeyCache($userId, $subscriberId), config('shaun_core.cache.time.model_query'), function () use ($userId, $subscriberId) {
            $paid = self::where('user_id', $userId)->where('subscriber_id', $subscriberId)->where('is_active', true)->first();

            return is_null($paid) ? false : $paid;
        });
    }

    public static function getKeyCache($userId, $subscriberId)
    {
        return 'user_subscriber _'.$userId.'_'.$subscriberId;
    }

    public static function getSubscriberCount($userId)
    {
        return self::where('subscriber_id', $userId)->count();
    }

    public function clearCache()
    {
        Cache::forget(self::getKeyCache($this->user_id, $this->subscriber_id));
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($subscriber) {
            $subscriber->clearCache();
            
            $userSubscriber = $subscriber->getSubscriber();
            if ($userSubscriber) {
                $count = self::getSubscriberCount($subscriber->subscriber_id);
                $userSubscriber->update([
                    'subscriber_count' => $count + 1
                ]);
            }
            
        });

        static::updated(function ($subscriber) {
            $subscriber->clearCache();
            if (! $subscriber->is_active) {
                $userSubscriber = $subscriber->getSubscriber();
                if ($userSubscriber) {
                    $count = self::getSubscriberCount($subscriber->subscriber_id);
                    $userSubscriber->update([
                        'subscriber_count' => $count - 1
                    ]);
                }
            }
        });
    }
}
