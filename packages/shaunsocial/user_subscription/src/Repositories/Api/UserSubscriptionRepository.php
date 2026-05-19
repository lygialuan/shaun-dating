<?php

namespace Packages\ShaunSocial\UserSubscription\Repositories\Api;

use Exception;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Http\Resources\Subscription\SubscriptionDetailResource;
use Packages\ShaunSocial\Core\Support\Facades\Subscription;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionStatus;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscription;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPlan;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;

class UserSubscriptionRepository
{   
    public function store($planId, $viewer)
    {
        $plan = UserSubscriptionPlan::findByField('id', $planId);
        $package = $plan->getPackage();

        DB::beginTransaction();
        $result = ['status' => false];
        try {
            $viewer->stopCurrentUserSubscription();
            $userSubscription = UserSubscription::create([
                'user_id' => $viewer->id,
                'role_id' => $package->role_id,
                'expire_role_id' => $package->expire_role_id,
                'plan_id' => $planId,
                'status' => UserSubscriptionStatus::ACTIVE
            ]);
            
            $subscription = Subscription::create($viewer, 'user_subscription', getWalletTokenName(), config('shaun_gateway.wallet_id'), $userSubscription, $plan, false);
            $userResult = Wallet::transferSubscription($subscription);
            $userSubscription->update([
                'subscription_id' => $subscription->id
            ]);
            if ($userResult['status']) {
                $transaction = $userResult['from_transaction'];
                $subscription->doActive([], true, $plan->amount, $transaction->getId());
                $result['status'] = true;
                DB::commit();
            } else {
                $result['message'] = __("You don't have enough balance");
                DB::rollBack();
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }
        
        return $result;
    }

    public function store_trial($planId, $viewer)
    {
        $plan = UserSubscriptionPlan::findByField('id', $planId);
        $package = $plan->getPackage();

        $viewer->stopCurrentUserSubscription();

        $userSubscription = UserSubscription::create([
            'user_id' => $viewer->id,
            'role_id' => $package->role_id,
            'expire_role_id' => $package->expire_role_id,
            'plan_id' => $planId,
            'status' => UserSubscriptionStatus::ACTIVE
        ]);

        $subscription = Subscription::create($viewer, 'user_subscription', getWalletTokenName(), config('shaun_gateway.wallet_id'), $userSubscription, $plan);
        $subscription->doActive([], false);
        $userSubscription->update([
            'subscription_id' => $subscription->id
        ]);

        $viewer->update([
            'user_subscription_has_trial' => true
        ]);
    }

    public function get_current($viewer)
    {
        $userSubscription = UserSubscription::getActive($viewer->id);
        if ($userSubscription) {
            $subscription = $userSubscription->getSubscription();
            $subscription->setIsDetail(true);
            return [
                'plan_id' => $subscription->package_id,
                'subscription' => new SubscriptionDetailResource($subscription)
            ];
        }
        
        return null;
    }
}
