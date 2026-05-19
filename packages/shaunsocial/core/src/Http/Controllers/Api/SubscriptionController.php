<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Subscription\GetSubscriptionDetailValidate;
use Packages\ShaunSocial\Core\Http\Requests\Subscription\GetSubscriptionValidate;
use Packages\ShaunSocial\Core\Http\Requests\Subscription\GetTransactionValidate;
use Packages\ShaunSocial\Core\Http\Requests\Subscription\StoreCancelSubscriptionValidate;
use Packages\ShaunSocial\Core\Http\Requests\Subscription\StoreResumeSubscriptionValidate;
use Packages\ShaunSocial\Core\Repositories\Api\SubscriptionRepository;

class SubscriptionController extends ApiController
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        parent::__construct();
    }

    public function config(Request $request)
    {
        $result = $this->subscriptionRepository->config($request->user());

        return $this->successResponse($result);
    }

    public function get(GetSubscriptionValidate $request)
    {
        $result = $this->subscriptionRepository->get($request->validated(), $request->user());

        return $this->successResponse($result);
    }

    public function get_detail(GetSubscriptionDetailValidate $request)
    {
        $result = $this->subscriptionRepository->get_detail($request->id);

        return $this->successResponse($result);
    }

    public function store_cancel(StoreCancelSubscriptionValidate $request)
    {
        $this->subscriptionRepository->store_cancel($request->id);

        return $this->successResponse();
    }

    public function store_resume(StoreResumeSubscriptionValidate $request)
    {
        $this->subscriptionRepository->store_resume($request->id);
        
        return $this->successResponse();
    }

    public function get_transaction(GetTransactionValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $result = $this->subscriptionRepository->get_transaction($request->id, $page);
        
        return $this->successResponse($result);
    }
}
