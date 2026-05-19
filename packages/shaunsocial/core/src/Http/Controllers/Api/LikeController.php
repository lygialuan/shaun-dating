<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Like\GetLikeValidate;
use Packages\ShaunSocial\Core\Http\Requests\Like\StoreLikeValidate;
use Packages\ShaunSocial\Core\Repositories\Api\LikeRepository;

class LikeController extends ApiController
{
    protected $likeRepository;

    public function __construct(LikeRepository $likeRepository)
    {
        $this->likeRepository = $likeRepository;
        parent::__construct();
    }

    public function store(StoreLikeValidate $request)
    {
        $result = $this->likeRepository->store($request->all(), $request->user());

        return $this->successResponse($result);
    }

    public function get(GetLikeValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->likeRepository->get($request->subject_type, $request->subject_id, $page, $request->user());
    
        return $this->successResponse($result);
    }
}
