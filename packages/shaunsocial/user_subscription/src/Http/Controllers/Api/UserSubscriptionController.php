<?php

namespace Packages\ShaunSocial\UserSubscription\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Models\Key;
use Packages\ShaunSocial\UserSubscription\Http\Requests\StoreUserSubscriptionTrialValidate;
use Packages\ShaunSocial\UserSubscription\Http\Requests\StoreUserSubscriptionValidate;
use Packages\ShaunSocial\UserSubscription\Http\Resources\UserSubscriptionPackageCompareResource;
use Packages\ShaunSocial\UserSubscription\Http\Resources\UserSubscriptionPackageResource;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPackage;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPackageCompare;
use Packages\ShaunSocial\UserSubscription\Repositories\Api\UserSubscriptionRepository;
use Illuminate\Http\Request;

class UserSubscriptionController extends ApiController
{
    protected $userSubscriptionRepository;
    
    public function __construct(UserSubscriptionRepository $userSubscriptionRepository)
    {
        if (! setting('shaun_user_subscription.enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }

        $this->userSubscriptionRepository = $userSubscriptionRepository;
        parent::__construct();
    }

    public function get_current(Request $request)
    {
        $result = $this->userSubscriptionRepository->get_current($request->user());

        return $this->successResponse($result);
    }
    
    public function config()
    {
        $highlightAs = Key::getValue('user_subscription_highlight_as', 'most_popular');
        $highlightBadgesList = getSubscriptionHighlightBadgeList();
        $packages = UserSubscriptionPackage::getAll()->filter(function ($value, $key) {
            return count($value->getPlans()) > 0;
        });
        $compares = UserSubscriptionPackageCompare::getAll();

        return $this->successResponse([
            'highlight_background_color' => getSubscriptionHighlightBackgroundColor(),
            'highlight_text_color' => getSubscriptionHighlightTextColor(),
            'highlight_text' => $highlightBadgesList[$highlightAs],
            'packages' => UserSubscriptionPackageResource::collection($packages),
            'compares' => UserSubscriptionPackageCompareResource::customCollection($compares, $packages)
        ]);
    }

    public function store(StoreUserSubscriptionValidate $request)
    {
        $result = $this->userSubscriptionRepository->store($request->plan_id, $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function store_trial(StoreUserSubscriptionTrialValidate $request)
    {
        $this->userSubscriptionRepository->store_trial($request->plan_id, $request->user());

        return $this->successResponse();
    }
}
