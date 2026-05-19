<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\ContentWarning\ContentWarningCategoryResource;
use Packages\ShaunSocial\Core\Models\ContentWarningCategory;

class ContentWarningCategoryRepository
{
    public function category()
    {
        $categories = ContentWarningCategory::getAll();
        return ContentWarningCategoryResource::collection($categories);
    }
}
