<?php


namespace Packages\ShaunSocial\Core\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait HasCacheSearch
{
    public static function bootHasCacheSearch()
    {
        static::created(function ($model) {
            $model->clearHasCacheSearch();
        });

        static::updating(function ($model) {
            $model->clearHasCacheSearchUpdate();
        });

        static::deleted(function ($model) {
            $model->clearHasCacheSearch();
        });
    }

    public static function getKeyCacheSearch($name)
    {
        return self::class.'_search_'.md5($name);
    }

    public function getListFieldSearch()
    {
        return property_exists($this, 'cacheSearchFields') ? $this->cacheSearchFields : [];
    }

    public function clearHasCacheSearchUpdate()
    {
        $listFields = $this->getListFieldSearch();
        foreach ($listFields as $field => $conditions) {
            if ($this->{$field} != $this->getOriginal($field)) {
                if ($this->{$field}) {
                    $this->clearHasCacheSearchValue($field, $this->{$field});
                }

                if ($this->getOriginal($field)) {
                    $this->clearHasCacheSearchValue($field, $this->getOriginal($field));
                }
            } else {
                if (count($conditions)) {
                    foreach ($conditions as $conditionField) {
                        if ($this->{$conditionField} != $this->getOriginal($conditionField)) {
                            $this->clearHasCacheSearchValue($field, $this->{$field});
                            break;
                        }
                    }
                }
            }
        }
    }

    public function clearHasCacheSearchValue($field, $value)
    {
        for ($i = 0; $i < Str::length($value); $i++) {
            $sub = Str::substr($value, $i, Str::length($value));
            for ($j = 0; $j < Str::length($sub); $j++) {
                Cache::forget($this->getKeyCacheSearch($field.'_'.Str::substr($sub, 0, $j + 1)));
            }
        }
    }

    public function clearHasCacheSearch()
    {
        $listFields = $this->getListFieldSearch();
        foreach ($listFields as $field => $conditions) {
            if ($this->{$field}) {
                $this->clearHasCacheSearchValue($field, $this->{$field});
            }
        }
    }

    public static function getCacheSearch($name, $builder, $time = null)
    {
        if (!$time) {
            $time = config('shaun_core.cache.time.search');
        }

        return Cache::remember(self::getKeyCacheSearch($name), $time, function () use ($builder) {
            return $builder->get();
        });
    }
}
