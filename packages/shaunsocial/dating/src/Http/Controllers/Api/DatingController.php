<?php

namespace Packages\ShaunSocial\Dating\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Dating\Repositories\Api\DatingRepository;
use Packages\ShaunSocial\Dating\Http\Requests\DatingSwipeRequest;

class DatingController extends ApiController
{
    protected $datingRepository;

    public function __construct(DatingRepository $datingRepository)
    {
        $this->datingRepository = $datingRepository;
        
        parent::__construct();
    }

    public function get_attributes(Request $request) {
        return $this->datingRepository->getAttributes();
    }

    public function save_attributes(Request $request)
    {
        $result = $this->datingRepository->saveAttributes($request->data, $request->user());

        return $this->successResponse($result);
    }

    public function get_interest_attributes(Request $request) {
        return $this->datingRepository->getInterestAttributes();
    }

    public function save_interest_attributes(Request $request)
    {
        $result = $this->datingRepository->saveInterestAttributes($request->data, $request->user());

        return $this->successResponse($result);
    }

    public function save_filter(Request $request)
    {
        $result = $this->datingRepository->saveFilter($request->data, $request->user());

        return $this->successResponse($result);
    }

    public function suggestion_locations(Request $request)
    {
        $result = $this->datingRepository->suggestionLocations($request->text);

        return $this->successResponse($result);
    }
       
    public function swipe(DatingSwipeRequest $request)
    {
        $result = $this->datingRepository->swipe($request->user(), $request->target_user_id, $request->action);

        return $this->successResponse($result);
    }

    public function get_user_actions(Request $request)
    {
        $result = $this->datingRepository->getUserActions($request->user(), $request->page, $request->action);

        return $this->successResponse($result);
    }
}