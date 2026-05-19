<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\UserList\DeleteListValidate;
use Packages\ShaunSocial\Core\Http\Requests\UserList\DeleteMemberValidate;
use Packages\ShaunSocial\Core\Http\Requests\UserList\GetMembersValidate;
use Packages\ShaunSocial\Core\Http\Requests\UserList\SendMessageValidate;
use Packages\ShaunSocial\Core\Http\Requests\UserList\StoreListValidate;
use Packages\ShaunSocial\Core\Http\Requests\UserList\StoreMembersValidate;
use Packages\ShaunSocial\Core\Repositories\Api\UserListRepository;

class UserListController extends ApiController
{
    protected $userListRepository;

    public function __construct(UserListRepository $userListRepository)
    {
        $this->userListRepository = $userListRepository;
        parent::__construct();
    }

    public function get_count(Request $request)
    {
        $result = $this->userListRepository->get_count($request->user());

        return $this->successResponse($result);
    }
    
    public function get(Request $request)
    {
        $page = $request->page ?? 1;

        $result = $this->userListRepository->get($page, $request->user());

        return $this->successResponse($result);
    }

    public function store(StoreListValidate $request)
    {
        $result = $this->userListRepository->store($request->validated(), $request->user());

        return $this->successResponse($result);
    }

    public function delete(DeleteListValidate $request)
    {
        $this->userListRepository->delete($request->id);

        return $this->successResponse();
    }

    public function get_members(GetMembersValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->userListRepository->get_members($request->query('query'), $page, $request->id);

        return $this->successResponse($result);
    }

    public function store_members(StoreMembersValidate $request)
    {
        $result = $this->userListRepository->store_members($request->id, $request->user_ids);

        return $this->successResponse($result);
    }

    public function delete_member(DeleteMemberValidate $request)
    {
        $this->userListRepository->delete_member($request->id);

        return $this->successResponse();
    }

    public function send_message(SendMessageValidate $request)
    {
        $this->userListRepository->send_message($request->validated(), $request->user());

        return $this->successResponse();
    }

    public function send_message_config(Request $request)
    {
        $result = $this->userListRepository->send_message_config($request->user());

        return $this->successResponse($result);
    }

    public function search_for_send(Request $request)
    {
        $result = $this->userListRepository->search_for_send($request->query('query'), $request->user());

        return $this->successResponse($result);
    }
}
