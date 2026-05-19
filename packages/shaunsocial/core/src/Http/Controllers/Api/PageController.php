<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Page\GetPageValidate;
use Packages\ShaunSocial\Core\Repositories\Api\PageRepository;

class PageController extends ApiController
{
    protected $pageRepository;

    public function getWhitelistForceLogin()
    {
        return [
            'get'
        ];
    }

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
        parent::__construct();
    }

    public function get(GetPageValidate $request)
    {
        $result = $this->pageRepository->get($request->slug);

        return $this->successResponse($result);
    }
}
