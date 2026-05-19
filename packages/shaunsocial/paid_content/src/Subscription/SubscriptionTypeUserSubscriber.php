<?php


namespace Packages\ShaunSocial\PaidContent\Subscription;

use Exception;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Subscription\SubscriptionBase;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriber;
use Packages\ShaunSocial\PaidContent\Notification\PaidContentSubscriberNotification;
use Packages\ShaunSocial\PaidContent\Notification\PaidContentSubscriberRemindNotification;
use Packages\ShaunSocial\PaidContent\Notification\PaidContentSubscriberStopNotification;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;

class SubscriptionTypeUserSubscriber extends SubscriptionBase
{
    public function getDescription($subscription)
    {
        return '';
    }

    public function hasFee()
    {
        return true;
    }

    public function getWalletType()
    {
        return 'paid_content';
    }

    public function getWalletTypeExtra()
    {
        return 'paid_content_user_subscriber';
    }

    public function handlePayment($subscription)
    {
        $refCode = '';
        if ($subscription->first_time_charge) {
            $params = $subscription->getParams();
            $refCode = $params['ref_code'] ?? '';
        }
        list($amount, $fee, $amountRef) = getAmountOfPaidContent($subscription->amount, [$subscription->subject_id, $subscription->user_id], $refCode);
        
        $result = ['status' => false];
        try {
            $userResult = Wallet::transfer($subscription->getClass()->getWalletType(), $subscription->user_id, $subscription->subject_id, $subscription->amount, $subscription, $subscription, $subscription->getClass()->getWalletTypeExtra(), [
                'fee' => $fee + $amountRef
            ]);

            $systemResult = ['status' => true];
            if ($fee) {
                $systemResult = Wallet::add('payment', config('shaun_wallet.system_wallet_user_id'), $fee, $subscription, 'root_subscriber_fee', $subscription->user_id);
            }
            $refResult = ['status' => true];
            if ($amountRef) {
                $userRef = User::findByField('ref_code', $refCode);
                $refResult = Wallet::add('commission', $userRef->id, $amountRef, $subscription, 'paid_content_user_subscriber', $subscription->user_id);
            }
            
            if ($userResult['status'] && $systemResult['status'] && $refResult['status']) {
                $result = $userResult;
                $user = $subscription->getSubject();
                $user->update([
                    'earn_amount' => $user->earn_amount + $subscription->amount,
                    'earn_fee_amount' => $user->earn_fee_amount + $fee,
                ]);
            } else {
                $result['message'] = __("You don't have enough balance");
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        } 

        return $result;
    }
    
    public function getName($isDetail = false)
    {
        return __('Profile subscription');
    }

    public function getCustomName($subscription)
    {
        $user = getUserIncludeDelete($subscription->getSubject());
        return __('Profile subscription'). ' - '. $user->getName();
    }

    public function doActive($subscription)
    {
        $user = $subscription->getSubject();
        $owner = $subscription->getUser();
        if ($user) {
            if ($subscription->first_time_active) {
                UserSubscriber::create([
                    'subscription_id' => $subscription->id,
                    'subscriber_id' => $user->id,
                    'user_id' => $owner->id
                ]);
            }
            
            //notify to user
            if ($subscription->first_time_active && $user->checkNotifySetting('new_subscriber')) {
                Notification::send($user, $owner, PaidContentSubscriberNotification::class, $owner, [], 'shaun_paid_content');
            }
        }
    }

    public function doRemind($subscription)
    {
        $user = $subscription->getSubject();
        $owner = $subscription->getUser();
        if ($user) {
            //notify to user
            Notification::send($owner, $owner, PaidContentSubscriberRemindNotification::class, $user, ['params' => ['subscription_id' => $subscription->id,'remind_day' => $subscription->remind_day]], 'shaun_paid_content');
        }
    }

    public function doCancel($subscription)
    {

    }

    public function doStop($subscription, $force)
    {
        $user = $subscription->getSubject();
        $owner = $subscription->getUser();
        if ($user && $owner) {
            $subscriber = UserSubscriber::findByField('subscription_id', $subscription->id);
            $subscriber->update([
                'is_active' => false
            ]);
            //notify to user
            Notification::send($owner, $owner, PaidContentSubscriberStopNotification::class, $user, ['params' => ['subscription_id' => $subscription->id]], 'shaun_paid_content');
        }
    }

    public function getRemindDay($subscription)
    {
        return setting('shaun_paid_content.user_subscriber_remind_day');
    }

    public function doResume($subscription)
    {
        
    }

    public function canContinue($subscription)
    {
        $userSubscription = $subscription->getSubject();
        $user = $subscription->getUser();

        if ($user && $userSubscription) {
            return true;
        }

        return false;
    }
}
