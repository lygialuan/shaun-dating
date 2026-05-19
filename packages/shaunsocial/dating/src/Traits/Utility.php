<?php


namespace Packages\ShaunSocial\Dating\Traits;

use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;

trait Utility
{
    use CacheSearchPagination;

    public function getCacheSearchListingPagination($name, $builder, $page)
    {
        return $this->getCacheSearchPagination($name, $builder, $page, 0, config('shaun_dating.search_pagination_time'));
    }
}
