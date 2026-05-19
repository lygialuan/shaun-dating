<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Hashtag\GetHashtagValidate;
use Packages\ShaunSocial\Core\Http\Requests\Hashtag\HashtagSearchValidate;
use Packages\ShaunSocial\Core\Repositories\Api\HashtagRepository;

class HashtagController extends ApiController
{
    protected $hashtagRepository;

    public function __construct(HashtagRepository $hashtagRepository)
    {
        $this->hashtagRepository = $hashtagRepository;
        parent::__construct();
    }

    public function get(GetHashtagValidate $request)
    {
        $result = $this->hashtagRepository->get($request->hashtag);

        return $this->successResponse($result);
    }

    public function suggest_search(HashtagSearchValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->hashtagRepository->suggest_search($request->user(), $request->query('query'), $page);
    
        return $this->successResponse($result);
    }

    public function suggest_signup(Request $request)
    {
        $result = $this->hashtagRepository->suggest_signup($request->user(), $request->text);
    
        return $this->successResponse($result);
    }

    public function trending(Request $request)
    {
        $result = $this->hashtagRepository->trending($request->user());

        return $this->successResponse($result);
    }

    public function suggest(Request $request)
    {
        $result = $this->hashtagRepository->suggest($request->user());

        return $this->successResponse($result);
    }

    public function trending_search(HashtagSearchValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->hashtagRepository->trending_search($request->query('query'), $page);
        
        return $this->successResponse($result);
    }

    public function search(Request $request)
    {
        $isCreate = $request->is_create ?? false;
        $result = $this->hashtagRepository->search($request->text, $isCreate);

        return $this->successResponse($result);
    }
}
