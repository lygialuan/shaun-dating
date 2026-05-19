<?php

namespace Packages\ShaunSocial\UserSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\GatewayRecurring\Models\OrderBase;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionOrderStatus;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionStatus;
use Packages\ShaunSocial\Core\Support\Facades\Subscription as SubscriptionFacade;
use Packages\ShaunSocial\Core\Models\Subscription;

class UserSubscriptionOrder extends Model implements OrderBase
{    
    use HasCacheQueryFields, IsSubject, HasUser;

    protected $cacheQueryFields = [
        'id',
        'gateway_recurring_transaction_id'
    ];

    protected $fillable = [
        'user_id',
        'gateway_recurring_id',
        'amount',
        'currency',
        'status',
        'exchange',
        'package_id',
        'params',
        'gateway_recurring_transaction_id'
    ];

    protected $casts = [
        'status' => UserSubscriptionOrderStatus::class
    ];

    public function onSuccessful($data, $transactionId)
    {
        if ($this->status == UserSubscriptionOrderStatus::DONE) {
            return;
        }

        $this->update([
            'status' => UserSubscriptionOrderStatus::DONE,
            'params' => json_encode($data),
            'gateway_recurring_transaction_id' => $transactionId
        ]);

        $viewer  = $this->getUser();
        $viewer->stopCurrentUserSubscription();

        $plan    = UserSubscriptionPlan::findByField('id', $this->package_id);
        $package = $plan->getPackage();

        $userSubscription = UserSubscription::create([
            'user_id'        => $viewer->id,
            'role_id'        => $package->role_id,
            'expire_role_id' => $package->expire_role_id,
            'plan_id'        => $this->package_id,
            'status'         => UserSubscriptionStatus::ACTIVE,
        ]);

        $subscription = SubscriptionFacade::create($viewer, 'user_subscription', getWalletTokenName(), 0, $userSubscription, $plan, false, $data, $this->gateway_recurring_id, $transactionId);
        $subscription->doActive([], true, $plan->amount, $transactionId);

        $userSubscription->update(['subscription_id' => $subscription->id]);
    }

    public function onFailed($transactionId)
    {
        $subscription= Subscription::findByField('gateway_recurring_transaction_id', $transactionId);
        $subscription->doStop();
    }

    public function getItems()
    {
        return [
            [
                'name' => __('Buy').' '. round($this->amount * $this->exchange, 2) .'('. getWalletTokenName(). ')',
                'quantity' => 1,
                'amount' => $this->amount,
                'currency' => $this->currency
            ]
        ];
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getTotalAmount()
    {
        return $this->amount;
    }

    public function getReturnUrl()
    {
        return route('web.user_subscription.index');
    }
    
    public function getCancelUrl()
    {
        return route('web.user_subscription.index');
    }
}
