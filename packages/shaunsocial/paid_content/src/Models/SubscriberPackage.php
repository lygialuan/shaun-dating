<?php


namespace Packages\ShaunSocial\PaidContent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Enum\SubscriptionBillingCycleType;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\PaidContent\Enum\SubscriberPackageType;
use Packages\ShaunSocial\Core\Traits\HasDeleted;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Traits\IsSubscriptionPackage;

class SubscriberPackage extends Model
{
    use HasCacheQueryFields, HasDeleted, IsSubscriptionPackage, IsSubject;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'amount',
        'type',
        'is_active',
        'order'
    ];

    protected $casts = [
        'type' => SubscriberPackageType::class,
        'billing_cycle_type' => SubscriptionBillingCycleType::class,
        'is_active' => 'boolean'
    ];

    protected $trial = 0;

    public function getTypeText()
    {
        $types = SubscriberPackageType::getAll();
        return $types[$this->type->value];
    }

    public function getName()
    {
        return __('User subscriber');
    }

    public function canSubscriber()
    {
        return $this->is_active && ! $this->isDeleted();
    }

    public function canChangeType()
    {
        if (! $this->id) {
            return true;
        }
        $useSubscriberPackager = UserSubscriberPackage::where('package_id', $this->id)->first();
        return ! $useSubscriberPackager;
    }

    public function setTrial($trial)
    {
        $this->trial = $trial;
    }

    public function getDescription()
    {
        $first = '';
        if ($this->trial) {
            $first = $this->trial.' '.__('day(s) trial then'). ' ';
        }

        return $first.formatNumber($this->amount). ' '.getWalletTokenName(). ' '. $this->getTypeText();
    }

    static function getAll()
    {
        return Cache::rememberForever('subscribe_packages', function () {
            return self::where('is_active', true)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    public function getTrialDay()
    {
        return $this->trial;
    }

    protected static function booted()
    {
        static::saving(function($package) {
            switch ($package->type) {
                case SubscriberPackageType::MONTHLY:
                    $package->billing_cycle = 1;
                    $package->billing_cycle_type = SubscriptionBillingCycleType::MONTH;
                    break;
                case SubscriberPackageType::QUARTERLY:
                    $package->billing_cycle = 3;
                    $package->billing_cycle_type = SubscriptionBillingCycleType::MONTH;
                    break;
                case SubscriberPackageType::BIANNUAL:
                    $package->billing_cycle = 6;
                    $package->billing_cycle_type = SubscriptionBillingCycleType::MONTH;
                    break;
                case SubscriberPackageType::ANNUAL:
                    $package->billing_cycle = 1;
                    $package->billing_cycle_type = SubscriptionBillingCycleType::YEAR;
                    break;
            }
        });

        static::deleting(function ($package) {
            Cache::forget('subscribe_packages');
        });

        static::saved(function ($package) {
            Cache::forget('subscribe_packages');
        });
    }
}
