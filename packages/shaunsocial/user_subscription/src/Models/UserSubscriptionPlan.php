<?php


namespace Packages\ShaunSocial\UserSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Traits\IsSubscriptionPackage;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionPlanBillingCycleType;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionStatus;
use Packages\ShaunSocial\GatewayRecurring\Models\GatewayRecurring;

class UserSubscriptionPlan extends Model
{
    use HasCacheQueryFields, HasDeleted, HasTranslations, IsSubject, IsSubscriptionPackage;
    
    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'name'
    ];
    
    protected $fillable = [
        'name',
        'amount',
        'package_id',
        'trial_day',
        'google_price_id',
        'apple_price_id',
        'order',
        'is_active',
        'gateway_recurring_id',
        'flex_form_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'billing_cycle_type' => UserSubscriptionPlanBillingCycleType::class
    ];

    public static function getPlansByPackageId($packageId)
    {
        return Cache::rememberForever(self::getCachePackageId($packageId), function () use ($packageId)  {
            return self::where('package_id', $packageId)->where('is_active', true)->orderBy('order', 'ASC')->orderBy('id','DESC')->get();
        });
    }

    public static function getCachePackageId($packageId)
    {
        return 'user_subscription_package_plans_'.$packageId;
    }

    public function getTrialDay()
    {
        return $this->trial_day;
    }

    public function getPackage()
    {
        return UserSubscriptionPackage::findByField('id', $this->package_id);
    }

    public function getName()
    {
        $package = $this->getPackage();
        return $package->name;
    }

    public function clearCache()
    {
        Cache::forget(self::getCachePackageId($this->package_id));
    }

    public function clearCacheTranslate()
    {
        $this->clearCache();
    }

    function getDescription()
    {
        return $this->amount. ' '.getWalletTokenName(). ' ' . $this->getTypeText();
    }

    function getTypeText()
    {
        switch ($this->billing_cycle_type) {
            case UserSubscriptionPlanBillingCycleType::DAY:
                if ($this->billing_cycle == 1) {
                    return __('daily');
                }
                return '/ '.$this->billing_cycle.' '.__('days');
                break;
            case UserSubscriptionPlanBillingCycleType::WEEK:
                if ($this->billing_cycle == 1) {
                    return __('weekly');
                }
                return '/ '.$this->billing_cycle.' '.__('weeks');
                break;
            case UserSubscriptionPlanBillingCycleType::MONTH:
                switch ($this->billing_cycle) {
                    case '1':
                        return __('monthly');
                        break;
                    case '3':
                        return __('quarterly');
                        break;
                    case '6':
                        return __('biannual');
                        break;
                }

                return '/ '.$this->billing_cycle.' '.__('months');
                break;
            case UserSubscriptionPlanBillingCycleType::YEAR:
                if ($this->billing_cycle == 1) {
                    return __('annual');
                }
                return '/ '.$this->billing_cycle.' '.__('years');
                break;
        }
    }

    public function canActive($userId, $trial = false)
    {
        if ($this->isDeleted()) {
            return false;
        }

        if (! $this->is_active) {
            return false;
        }

        if ($trial && ! $this->trial_day) {
            return false;
        }

        $package = UserSubscriptionPackage::findByField('id', $this->package_id);

        if ($package->isDeleted()) {
            return false;
        }

        if (! $package->is_active) {
            return false;
        }

        $userSubscription = UserSubscription::getActive($userId);
        if ($userSubscription && in_array($userSubscription->status, [UserSubscriptionStatus::ACTIVE, UserSubscriptionStatus::CANCEL])) {
            return $userSubscription->plan_id != $this->id;
        }

        return true;
    }
        
    public function gateways()
    {
        return $this->belongsToMany(
            GatewayRecurring::class,
            'plan_gateway_recurrings',
            'plan_id',
            'gateway_recurring_id'
        )->withPivot('flex_form_id')->withTimestamps();
    }

    protected static function booted()
    {
        parent::booted();

        static::saved(function ($plan) {
            $plan->clearCache();
        });
    }
}
