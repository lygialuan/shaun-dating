<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Widget;

use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;

class UserSuggestWidget extends BaseWidget
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getData($request, $params = [])
    {
        $viewer = $request->user();
        $result = $this->userRepository->suggest($viewer, $params['item_number']);
        return count($result) ? $result->values()->toArray($request) : false;
    }
}
