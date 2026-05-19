<?php


namespace Packages\ShaunSocial\Core\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheSearchPagination
{
    public function getCacheSearchPagination($name, $builder, $page, $limit = 0, $ttl = null)
    {
        if (! $limit) {
            $limit = setting('feature.item_per_page');
        }

        if (! $ttl) {
            $ttl = config('shaun_core.cache.time.search_pagination');
        }

        return Cache::remember('cache_sort_pagination_'.md5($name).'_page_'.$page, $ttl, function () use ($builder, $page, $limit) {
            return $builder->offset(($page - 1) * $limit)->limit($limit)->get();
        });
    }

    public function getCacheSearchShortPagination($name, $builder, $page, $limit = 0)
    {
        return $this->getCacheSearchPagination($name, $builder, $page, $limit, config('shaun_core.cache.time.search_short_pagination'));
    }
}
