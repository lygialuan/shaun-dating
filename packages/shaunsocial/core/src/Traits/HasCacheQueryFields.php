<?php


namespace Packages\ShaunSocial\Core\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCacheQueryFields
{
    static $cacheQueryFieldsResult = [];

    public static function bootHasCacheQueryFields()
    {
        static::created(function ($model) {
            $model->clearCacheQueryFields();
        });

        static::updating(function ($model) {
            $model->clearCacheQueryFields(true);
        });

        static::deleted(function ($model) {
            $model->clearCacheQueryFields();
        });
    }

    public static function setCacheQueryFieldsResult($field, $fieldValue, $value)
    {
        self::$cacheQueryFieldsResult[self::getKeyCacheQueryField($field, $fieldValue)] = $value;
    }

    public static function getKeyCacheQueryField($attribute, $key)
    {
        return static::class.'_find_by_'.$attribute.'_'.$key;
    }

    public function getCacheQueryFieldAttributes()
    {
        return property_exists($this, 'cacheQueryFields') ? $this->cacheQueryFields : [];
    }

    public static function clearCacheQueryFieldsByAttribute($attribute, $value)
    {
        Cache::forget(self::getKeyCacheQueryField($attribute, $value));
    }

    public function clearCacheQueryFields($update = false)
    {
        $cacheFieldAttributes = $this->getCacheQueryFieldAttributes();
        foreach ($cacheFieldAttributes as $attribute) {
            $value = $this->{$attribute} ;
            Cache::forget($this->getKeyCacheQueryField($attribute, $value));
            if ($update) {
                $value = $this->getOriginal($attribute);
                Cache::forget($this->getKeyCacheQueryField($attribute, $value));
            }
        }
    }

    public static function findByField($field, $value, $list = false, $clear = false)
    {
        if (!isset(self::$cacheQueryFieldsResult[self::getKeyCacheQueryField($field, $value)]) || $clear) {
            self::$cacheQueryFieldsResult[self::getKeyCacheQueryField($field, $value)] = Cache::remember(self::getKeyCacheQueryField($field, $value), config('shaun_core.cache.time.model_query'), function () use ($field, $value, $list) {
                if ($list) {
                    return self::where($field, $value)->get();
                } else {
                    return self::where($field, $value)->withoutGlobalScopes()->first();
                }
            });
        }

        return self::$cacheQueryFieldsResult[self::getKeyCacheQueryField($field, $value)];
    }
}
