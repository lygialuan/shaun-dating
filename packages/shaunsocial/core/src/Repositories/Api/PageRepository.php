<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Page\PageResource;
use Packages\ShaunSocial\Core\Models\Page;
use Packages\ShaunSocial\Core\Traits\ApiResponser;

class PageRepository
{
    use ApiResponser;

    public function get($slug)
    {
        $page = Page::findByField('slug', $slug);

        return new PageResource($page);
    }
}
