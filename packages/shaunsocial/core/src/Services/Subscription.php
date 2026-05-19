<?php


namespace Packages\ShaunSocial\Core\Services;

use Packages\ShaunSocial\Core\Models\Subscription as ModelsSubscription;

class Subscription
{
    public function create($user, $type, $currency, $gatewayId ,$subject, $package, $supportTrial = true, $params = [], $gatewayRecurringId = 0, $transactionId = 0)
    {
        $subscription = ModelsSubscription::create([
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $package->getAmount(),
            'currency' => $currency,
            'gateway_id' => $gatewayId,
            'gateway_recurring_id' => $gatewayRecurringId,
            'gateway_recurring_transaction_id' => $transactionId,
            'subject_type' => $subject->getSubjectType(),
            'subject_id' => $subject->id,
            'package_type' => $package->getSubjectType(),
            'package_id' => $package->id,
            'package_name' => $package->getName(),
            'billing_cycle' => $package->getBillingCycle(),
            'billing_cycle_type' => $package->getBillingCycleType(),
            'trial_day' => $supportTrial ? $package->getTrialDay() : 0,
            'trial_amount' => $package->getTrialAmount(),
            'params' => json_encode($params)
        ]);

        return $subscription;
    }
}
