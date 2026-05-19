<?php

namespace Packages\ShaunSocial\Gift\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Gift\Repositories\Api\GiftRepository;
use Packages\ShaunSocial\Gift\Http\Requests\SendGiftRequest;
use Packages\ShaunSocial\Gift\Http\Requests\GetGiftReceivedRequest;

class GiftController extends ApiController
{
    protected $giftRepository;

    public function __construct(GiftRepository $giftRepository)
    {
        $this->giftRepository = $giftRepository;
        
        parent::__construct();
    }

    public function send(SendGiftRequest $request) {
        $result = $this->giftRepository->send($request->user(), $request->validated());
        
        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function gift_received(GetGiftReceivedRequest $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->giftRepository->giftReceived($request->id, $page);

        return $this->successResponse($result);
    }
}