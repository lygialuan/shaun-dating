<?php


namespace Packages\ShaunSocial\PaidContent\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetPostProfileValidate;
use Packages\ShaunSocial\PaidContent\Repositories\Api\PaidContentRepository;
use Packages\ShaunSocial\PaidContent\Http\Requests\GetConfigValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\GetEarningReportValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\GetEarningTransactionValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\GetPackageValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\GetProfilePackageValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\GetSubscriberDetailValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\GetSubscriberTransactionValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\GetUserSubscriberValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\StoreEditPostValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\StorePaidPostValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\StoreSubscriberCancelValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\StoreSubscriberResumeValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\StoreUserPackageValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\StoreSubscriberUserValidate;
use Packages\ShaunSocial\PaidContent\Http\Requests\StoreTipValidate;

class PaidContentController extends ApiController
{
    protected $paidContentRepository;

    public function __construct(PaidContentRepository $paidContentRepository)
    {
        if (! setting('shaun_paid_content.enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }
        
        $this->paidContentRepository = $paidContentRepository;
    }

    public function store_paid_post(StorePaidPostValidate $request)
    {
        $refCode = $request->input('ref_code');
        $result = $this->paidContentRepository->store_paid_post($request->id, $refCode, $request->user());
        
        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function get_config(GetConfigValidate $request)
    {
        $result = $this->paidContentRepository->get_config($request->user());

        return $this->successResponse($result);
    }

    public function store_subscriber_user(StoreSubscriberUserValidate $request)
    {
        $refCode = $request->input('ref_code');
        $result = $this->paidContentRepository->store_subscriber_user($request->id, $refCode ,$request->user());

        return $this->successResponse($result);
    }

    public function get_packages(GetPackageValidate $request)
    {
        $result = $this->paidContentRepository->get_packages($request->user());

        return $this->successResponse($result);
    }

    public function get_profile_packages(GetProfilePackageValidate $request)
    {
        $result = $this->paidContentRepository->get_profile_packages($request->id, $request->user());

        return $this->successResponse($result);
    }

    public function store_user_package(StoreUserPackageValidate $request)
    {
        $this->paidContentRepository->store_user_package($request->validated(), $request->user());

        return $this->successResponse();
    }

    public function get_earning_report(GetEarningReportValidate $request)
    {
        $result = $this->paidContentRepository->get_earning_report($request->user());

        return $this->successResponse($result);
    }

    public function get_earning_transaction(GetEarningTransactionValidate $request)
    {
        $result = $this->paidContentRepository->get_earning_transaction($request->user());

        return $this->successResponse($result);
    }

    public function get_user_subscriber(GetUserSubscriberValidate $request)
    {
        $request->mergeIfMissing([
            'page' => 1,
            'from_date' => '',
            'to_date' => '',
        ]);
        
        $result = $this->paidContentRepository->get_user_subscriber($request->validated(), $request->user());

        return $this->successResponse($result);
    }

    public function get_subscriber_detail(GetSubscriberDetailValidate $request)
    {
        $result = $this->paidContentRepository->get_subscriber_detail($request->id);

        return $this->successResponse($result);
    }

    public function store_subscriber_cancel(StoreSubscriberCancelValidate $request)
    {
        $this->paidContentRepository->store_subscriber_cancel($request->id);

        return $this->successResponse();
    }

    public function store_subscriber_resume(StoreSubscriberResumeValidate $request)
    {
        $this->paidContentRepository->store_subscriber_resume($request->id);

        return $this->successResponse();
    }

    public function get_subscriber_transaction(GetSubscriberTransactionValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $result = $this->paidContentRepository->get_subscriber_transaction($request->id, $page);
        
        return $this->successResponse($result);
    }
    
    public function get_tip_packages(Request $request)
    {
        $result = $this->paidContentRepository->get_tip_packages();
        
        return $this->successResponse($result);
    }

    public function store_tip(StoreTipValidate $request)
    {
        $request->mergeIfMissing([
            'amount' => '0',
            'package_id' => '0'
        ]);

        $result = $this->paidContentRepository->store_tip($request->only([
            'package_id', 'amount', 'user_id'
        ]), $request->user());

        return $this->successResponse($result);
    }

    public function get_my_paid_post(Request $request)
    {
        $page = $request->page ?? 1;

        $result = $this->paidContentRepository->get_my_paid_post($page, $request->user());

        return $this->successResponse($result);
    }

    public function get_profile_paid_post(GetPostProfileValidate $request)
    {
        $page = $request->page ?? 1;

        $result = $this->paidContentRepository->get_profile_paid_post($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function store_edit_post(StoreEditPostValidate $request)
    {
        $result = $this->paidContentRepository->store_edit_post($request->validated());

        return $this->successResponse($result);
    }
}