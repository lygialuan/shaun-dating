<?php


namespace Packages\ShaunSocial\UserPage\Repositories\Helpers\Widget;

use Packages\ShaunSocial\Core\Repositories\Helpers\Widget\BaseWidget;
use Packages\ShaunSocial\UserPage\Repositories\Api\UserPageRepository;

class PageFeatureWidget extends BaseWidget
{
    protected $userPageRepository;

    public function __construct(UserPageRepository $userPageRepository)
    {
        $this->userPageRepository = $userPageRepository;
    }

    public function getData($request, $params = [])
    {
        $viewer = $request->user();
        $result = $this->userPageRepository->get_feature($viewer, 1, $params['item_number']);
        return count($result) ? $result->values()->toArray($request) : false;
    }
}
