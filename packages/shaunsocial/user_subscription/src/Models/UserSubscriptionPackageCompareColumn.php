<?php


namespace Packages\ShaunSocial\UserSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionPackageCompareColumnType;

class UserSubscriptionPackageCompareColumn extends Model
{
    use HasCacheQueryFields, HasTranslations;
    
    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'value'
    ];
    
    protected $fillable = [
        'value',
        'type',
        'compare_id',
        'package_id'
    ];

    protected $casts = [
        'type' => UserSubscriptionPackageCompareColumnType::class
    ];

    public static function getColumnByCompareIdPackageId($compareId, $packageId)
    {
        return Cache::rememberForever(self::getCacheCompareIdPackageId($compareId, $packageId), function () use ($compareId, $packageId)  {
            return self::where('package_id', $packageId)->where('compare_id', $compareId)->first();
        });
    }

    public static function getCacheCompareIdPackageId($compareId, $packgeId)
    {
        return 'user_subscription_compare_columns_'.$compareId.'_'.$packgeId;
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheCompareIdPackageId($this->compare_id, $this->package_id));
    }

    public function isText()
    {
        return $this->type == UserSubscriptionPackageCompareColumnType::TEXT;
    }
    
    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($column) {
            $column->clearCache();
        });

        static::saved(function ($column) {
            $column->clearCache();
        });
    }
}
