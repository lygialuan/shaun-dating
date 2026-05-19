<?php

namespace Packages\ShaunSocial\GatewayRecurring\Repositories\Api;

use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPlan;
use Packages\ShaunSocial\GatewayRecurring\Models\GatewayRecurring;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionOrder;
use Packages\ShaunSocial\Core\Models\Currency;

class GatewayRecurringRepository
{
    public function create_link_payment($viewer, $planId, $gatewayRecurringId, $flexFormId)
    {
        $plan = UserSubscriptionPlan::findByField('id', $planId);
        if(!$plan) return;

        $package = $plan->getPackage();

        $currencyDefault = Currency::getDefault();

        $exchangeRate = getWalletExchangeRate();

        $order = UserSubscriptionOrder::create([
            'amount'               => round($plan->amount / $exchangeRate,2),
            'currency'             => $currencyDefault->code,
            'gateway_recurring_id' => $gatewayRecurringId,
            'user_id'              => $viewer->id,
            'package_id'           => $plan->id,
            'exchange'             => $exchangeRate
        ]);

        $gateway = GatewayRecurring::findByField('id', $gatewayRecurringId);

        $result  = $gateway->createLinkPayment($order, $flexFormId);

        return $result;
    }
}
