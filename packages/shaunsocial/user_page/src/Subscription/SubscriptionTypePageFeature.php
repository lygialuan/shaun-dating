<?php


namespace Packages\ShaunSocial\UserPage\Subscription;

use Packages\ShaunSocial\Core\Subscription\SubscriptionBase;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\UserPage\Notification\UserPageFeatureActiveNotification;
use Packages\ShaunSocial\UserPage\Notification\UserPageFeatureRemindNotification;
use Packages\ShaunSocial\UserPage\Notification\UserPageFeatureStopNotification;

class SubscriptionTypePageFeature extends SubscriptionBase
{
    public function getDescription($subscription)
    {
        return '';
    }

    public function getWalletTypeExtra()
    {
        return 'user_page_feature_buy';
    }
    
    public function getName($isDetail = false)
    {
        return __('Page feature');
    }

    public function doActive($subscription)
    {
        $user = $subscription->getSubject();
        if ($user) {
            $data = [
                'is_page_feature' => true
            ];

            //notify to user
            if ($subscription->first_time_active) {
                $data['page_feature_view'] = 0;
                Notification::send($user, $user, UserPageFeatureActiveNotification::class, null, [], 'shaun_user_page');
            }

            $user->update($data);
        }
    }

    public function doRemind($subscription)
    {
        $user = $subscription->getSubject();
        if ($user) {
            //notify to user
            Notification::send($user, $user, UserPageFeatureRemindNotification::class, null, ['params' => ['subscription_id' => $subscription->id,'remind_day' => $subscription->remind_day]], 'shaun_user_page');
        }
    }

    public function doCancel($subscription)
    {

    }

    public function doStop($subscription, $force)
    {
        $user = $subscription->getSubject();
        if ($user) {
            $user->update([
                'is_page_feature' => false
            ]);
            //notify to user
            Notification::send($user, $user, UserPageFeatureStopNotification::class, null, ['params' => ['subscription_id' => $subscription->id]], 'shaun_user_page');
        }
    }

    public function getRemindDay($subscription)
    {
        return setting('shaun_user_page.feature_remind_day');
    }

    public function doResume($subscription)
    {
        
    }
}
