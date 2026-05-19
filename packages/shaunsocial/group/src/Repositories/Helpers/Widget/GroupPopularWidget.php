<?php


namespace Packages\ShaunSocial\Group\Repositories\Helpers\Widget;

use Packages\ShaunSocial\Core\Repositories\Helpers\Widget\BaseWidget;
use Packages\ShaunSocial\Group\Repositories\Api\GroupRepository;

class GroupPopularWidget extends BaseWidget
{
    protected $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function getData($request, $params = [])
    {
        $viewer = $request->user();
        $result = $this->groupRepository->get_popular($viewer, $params['item_number']);
        return count($result) ? $result->values()->toArray($request) : false;
    }
}
