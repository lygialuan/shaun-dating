<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Repositories\Api\ContentWarningCategoryRepository;

class ContentWarningController extends ApiController
{
    protected $ContentWarningCategoryRepository;

    public function __construct(ContentWarningCategoryRepository $ContentWarningCategoryRepository)
    {
        $this->ContentWarningCategoryRepository = $ContentWarningCategoryRepository;
        parent::__construct();
    }

    public function category()
    {
        $result = $this->ContentWarningCategoryRepository->category();

        return $this->successResponse($result);
    }
}
