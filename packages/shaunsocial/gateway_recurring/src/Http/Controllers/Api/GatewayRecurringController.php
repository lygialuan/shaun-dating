<?php

namespace Packages\ShaunSocial\GatewayRecurring\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\GatewayRecurring\Repositories\Api\GatewayRecurringRepository;

class GatewayRecurringController extends ApiController
{
    protected $gatewayRecurringRepository;

    public function __construct(GatewayRecurringRepository $gatewayRecurringRepository)
    {
        $this->gatewayRecurringRepository = $gatewayRecurringRepository;
    }

    public function create_link_payment(Request $request)
    {
        $result = $this->gatewayRecurringRepository->create_link_payment($request->user(), $request->plan_id, $request->gateway_recurring_id, $request->flex_form_id);
        return $this->successResponse($result);
    }
}
