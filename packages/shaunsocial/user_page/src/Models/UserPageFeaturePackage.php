<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Enum\SubscriptionBillingCycleType;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Traits\IsSubscriptionPackage;
use Packages\ShaunSocial\UserPage\Enum\UserPageFeaturePackageType;

class UserPageFeaturePackage extends Model
{   
    use HasDeleted, IsSubject, IsSubscriptionPackage, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'name',
        'amount',
        'type',
        'is_active',
        'order'
    ];

    protected $casts = [
        'type' => UserPageFeaturePackageType::class,
        'billing_cycle_type' => SubscriptionBillingCycleType::class,
        'is_active' => 'boolean'
    ];
    
    static function getAll()
    {
        return Cache::rememberForever('user_page_feature_packages', function () {
            return self::where('is_active', true)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    public function getTypeText()
    {
        $types = UserPageFeaturePackageType::getAll();
        return $types[$this->type->value];
    }

    public function getDescription()
    {
        return formatNumber($this->amount). ' '.getWalletTokenName(). ' '. $this->getTypeText();
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($package) {
            Cache::forget('user_page_feature_packages');
        });

        static::saved(function ($package) {
            Cache::forget('user_page_feature_packages');
        });

        static::saving(function($package) {
            switch ($package->type) {
                case UserPageFeaturePackageType::MONTHLY:
                    $package->billing_cycle = 1;
                    $package->billing_cycle_type = SubscriptionBillingCycleType::MONTH;
                    break;
                case UserPageFeaturePackageType::QUARTERLY:
                    $package->billing_cycle = 3;
                    $package->billing_cycle_type = SubscriptionBillingCycleType::MONTH;
                    break;
                case UserPageFeaturePackageType::BIANNUAL:
                    $package->billing_cycle = 6;
                    $package->billing_cycle_type = SubscriptionBillingCycleType::MONTH;
                    break;
                case UserPageFeaturePackageType::ANNUAL:
                    $package->billing_cycle = 1;
                    $package->billing_cycle_type = SubscriptionBillingCycleType::YEAR;
                    break;
            }
        });
    }
}
