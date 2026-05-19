<?php


namespace Packages\ShaunSocial\UserSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class UserSubscriptionPackageCompare extends Model
{
    use HasCacheQueryFields, HasTranslations;
    
    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'name'
    ];
    
    protected $fillable = [
        'name'
    ];

    public static function getAll()
    {
        return Cache::rememberForever('user_subscription_package_compares', function () {
            return self::orderBy('id', 'ASC')->get();
        });
    }

    function getColumn($packageId)
    {
        return UserSubscriptionPackageCompareColumn::where('package_id', $packageId)->where('compare_id', $this->id)->orderBy('id', 'DESC')->first();
    }

    public function clearCache()
    {
        Cache::forget('user_subscription_packages');
    }
    
    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($compare) {
            $compare->clearCache();
        });

        static::saved(function ($compare) {
            $compare->clearCache();
        });
    }
}
