<?php


namespace Packages\ShaunSocial\UserSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\Subscription;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionStatus;

class UserSubscription extends Model
{
    use HasCacheQueryFields, IsSubject, HasUser;
    
    protected $cacheQueryFields = [
        'id'
    ];
    
    protected $fillable = [
        'role_id',
        'expire_role_id',
        'user_id',
        'plan_id',
        'status',
        'subscription_id'
    ];

    protected $casts = [
        'status' => UserSubscriptionStatus::class,
    ];

    public static function getActive($userId)
    {
        return Cache::remember(self::getCacheActiveUserId($userId), config('shaun_core.cache.time.model_query'), function () use ($userId) {
            $userSubscription = self::where('user_id', $userId)->orderBy('id', 'DESC')->first();

            return is_null($userSubscription) ? false : ($userSubscription->isActive() ? $userSubscription : false);
        });
    }

    public static function getCacheActiveUserId($userId)
    {
        return 'user_subscriptions_user_active_'.$userId;
    }

    public function isActive()
    {
        return in_array($this->status, [UserSubscriptionStatus::ACTIVE, UserSubscriptionStatus::CANCEL]);
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheActiveUserId($this->user_id));
    }

    public function getSubscription()
    {
        return Subscription::findByField('id', $this->subscription_id);
    }

    public function getPlan()
    {
        return UserSubscriptionPlan::findByField('id', $this->plan_id);
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($userSubscription) {
            $userSubscription->clearCache();
        });

        static::saved(function ($userSubscription) {
            $userSubscription->clearCache();
        });
    }
}
