<?php

namespace Packages\ShaunSocial\Dating\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class DatingAttribute extends Model
{
    use HasCacheQueryFields, HasTranslations, HasStorageFiles;

    public static $cachePermanentKey = 'dating_attributes';

    protected $cacheQueryFields = [
        'id',
    ];

    protected $translatable = [
        'name',
    ];

    protected $storageFields = [
        'icon_file_id'
    ];

    protected $fillable = [
        'name',
        'category_id',
        'icon_file_id',
        'order',
        'is_active',
        'allow_multiple'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public static function getAttributeList() {
        return Cache::rememberForever(
            self::$cachePermanentKey,
            function () {
                return self::orderBy('order')
                    ->where('is_active', true)
                    ->get();
        });
    }

    public static function getByCategoryId($categoryId)
    {
        return Cache::remember(
            self::getCacheAttributeByCategoryIdName($categoryId),
            config('shaun_dating.cache.time.1_day'),
            function () use ($categoryId) {
                return self::where('category_id', $categoryId)
                    ->orderBy('order')
                    ->get();
            }
        );
    }

    public static function getCacheAttributeByCategoryIdName($categoryId)
    {
        return 'attributes_' . $categoryId;
    }

    public static function getCacheAttributeValuesName($attributeId)
    {
        return "attribute_values_{$attributeId}";
    }

    public static function getAttributeValues($attributeId)
    {
        return Cache::remember(
            self::getCacheAttributeValuesName($attributeId),
            config('shaun_dating.cache.time.1_day'),
            function () use ($attributeId) {
                return DatingAttributeValue::where('attribute_id', $attributeId)
                    ->get();
            }
        );
    }

    public function canUse()
    {
        return $this->is_active;
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheAttributeByCategoryIdName(0));
        Cache::forget(self::$cachePermanentKey);
    }

    protected static function booted()
    {
        parent::booted();

        static::saving(function ($categoryTag) {
            $categoryTag->clearCache();
        });

        static::deleting(function ($categoryTag) {
            $categoryTag->clearCache();
        });

        static::updated(function ($categoryTag) {
            $categoryTag->clearCache();
        });

        static::creating(function ($category) {
            $last = self::latest()->first();
            if ($last) {
                $category->order = $last->id;
            }
        });
    }

    public static function getListAttributesByIds($ids)
    {
        return Cache::remember(
            self::getListAttributeCacheName($ids),
            config('shaun_dating.cache.time.30_min'),
            function () use ($ids) {
                return self::whereIn('id', $ids)->get();
            }
        );
    }

    public static function getListAttributeCacheName($ids)
    {
        $idsStr = implode('_', $ids);
        return "get_list_attribute_{$idsStr}";
    }

    public function clearCacheTranslate()
    {
        $this->clearCacheQueryFields();
        $this->clearCache();
    }

    public function getIconUrl()
    {
        $iconFile = $this->getFile('icon_file_id');
        return $iconFile ? $iconFile->getUrl() : null;
    }
}
