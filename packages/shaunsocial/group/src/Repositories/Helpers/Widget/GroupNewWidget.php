<?php


namespace Packages\ShaunSocial\Group\Repositories\Helpers\Widget;

use Packages\ShaunSocial\Core\Repositories\Helpers\Widget\BaseWidget;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Repositories\Api\GroupRepository;

class GroupNewWidget extends BaseWidget
{
    protected $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function getData($request, $params = [])
    {
        $viewer = $request->user();
        $result = $this->groupRepository->get_new($viewer, $params['item_number']);
        return count($result) ? $result->values()->toArray($request) : false;
    }

    public function saveData($contentId, $paramsOld = [], $params = [])
    {
        Group::clearCacheGroupNew();
    }
}
