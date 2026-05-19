<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Follow\HashtagStoreFollowValidate;
use Packages\ShaunSocial\Core\Http\Requests\Follow\UserGetFollowerValidate;
use Packages\ShaunSocial\Core\Http\Requests\Follow\UserGetFollowingValidate;
use Packages\ShaunSocial\Core\Http\Requests\Follow\UserGetMyFollowerValidate;
use Packages\ShaunSocial\Core\Http\Requests\Follow\UserGetMyFollowingValidate;
use Packages\ShaunSocial\Core\Http\Requests\Follow\UserStoreFollowNotificationValidate;
use Packages\ShaunSocial\Core\Http\Requests\Follow\UserStoreFollowValidate;
use Packages\ShaunSocial\Core\Repositories\Api\FollowRepository;

class FollowController extends ApiController
{
    protected $followRepository;

    public function __construct(FollowRepository $followRepository)
    {
        $this->followRepository = $followRepository;
        parent::__construct();
    }

    public function user_store(UserStoreFollowValidate $request)
    {
        $this->followRepository->user_store($request->all(), $request->user());

        return $this->successResponse();
    }

    public function user_get_follower(UserGetFollowerValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->followRepository->user_get_follower($request->id, $page, 'all', $request->user());
    
        return $this->successResponse($result);
    }

    public function user_get_following(UserGetFollowingValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->followRepository->user_get_following($request->id, $page, 'all', $request->user());
    
        return $this->successResponse($result);
    }

    public function user_get_my_follower(UserGetMyFollowerValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $type = $request->type ? $request->type : 'user';

        $result = $this->followRepository->user_get_follower($request->user()->id, $page, $type, $request->user());
    
        return $this->successResponse($result);
    }

    public function user_get_my_following(UserGetMyFollowingValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $type = $request->type ? $request->type : 'user';

        $result = $this->followRepository->user_get_following($request->user()->id, $page, $type, $request->user());
    
        return $this->successResponse($result);
    }

    public function user_store_notification(UserStoreFollowNotificationValidate $request)
    {
        $this->followRepository->user_store_notification($request->all(), $request->user());
    
        return $this->successResponse();
    }

    public function hashtag_store(HashtagStoreFollowValidate $request)
    {
        $this->followRepository->hashtag_store($request->all(), $request->user());
    
        return $this->successResponse();
    }

    public function hashtag(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->followRepository->hashtag($request->user(), $page);

        return $this->successResponse($result);
    }

}
