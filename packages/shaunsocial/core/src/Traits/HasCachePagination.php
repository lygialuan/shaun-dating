<?php


namespace Packages\ShaunSocial\Core\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCachePagination
{
    public static function bootHasCachePagination()
    {
        static::created(function ($model) {
            $model->clearHasCachePagination();
        });

        static::updating(function ($model) {
            if ($model->checkClearCachePagination()) {
                $model->clearHasCachePagination();
            }
        });

        static::deleted(function ($model) {
            $model->clearHasCachePagination();
        });
    }

    public function checkClearCachePagination()
    {
        $listFields = method_exists($this, 'getListFieldPagination') ? $this->getListFieldPagination() : [];
        foreach ($listFields as $field) {
            if ($this->{$field} != $this->getOriginal($field)) {
                return true;
            }
        }

        return false;
    }

    public static function getKeyCachePagination($name)
    {
        return self::class.'_pagination_'.$name;
    }

    public function clearHasCachePagination()
    {
        $listCaches = method_exists($this, 'getListCachePagination') ? $this->getListCachePagination() : [];
        if (config('cache.default')) {
            foreach ($listCaches as $name) {
                if (in_array(config('cache.default'), config('shaun_core.cache.tags'))) {
                    Cache::tags(self::getKeyCachePagination($name))->flush();
                } else {
                    for ($i = 1; $i < config('shaun_core.pagination.max_page_clear_cache'); $i++) {
                        $key = self::getKeyCachePagination($name).'_page_'.$i;
                        if (Cache::has($key)) {
                            Cache::forget($key);
                        } else {
                            break;
                        }
                    }
                }
            }
        }
    }

    public static function getCachePagination($name, $builder, $page, $limit = 0)
    {
        if (! $limit) {
            $limit = setting('feature.item_per_page');
        }

        if (in_array(config('cache.default'), config('shaun_core.cache.tags'))) {
            $data = Cache::tags(self::getKeyCachePagination($name))->get('page_'.$page);

            if (! $data) {
                $data = self::getDataPagination($builder, $page, $limit);
                Cache::tags($name)->put('page_'.$page, $data, config('shaun_core.cache.time.pagination'));
            }

            return $data;
        } else {
            return Cache::remember(self::getKeyCachePagination($name).'_page_'.$page, config('shaun_core.cache.time.pagination'), function () use ($builder, $page, $limit) {
                return self::getDataPagination($builder, $page, $limit);
            });
        }
    }

    public static function getDataPagination($builder, $page, $limit)
    {
        return $builder->offset(($page - 1) * $limit)->limit($limit)->get();
    }
}
