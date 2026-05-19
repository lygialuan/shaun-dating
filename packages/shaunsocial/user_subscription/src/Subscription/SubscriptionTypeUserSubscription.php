<?php


namespace Packages\ShaunSocial\UserSubscription\Subscription;

use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Subscription\SubscriptionBase;
use Packages\ShaunSocial\Core\Support\Facades\Mail;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionStatus;
use Packages\ShaunSocial\UserSubscription\Notification\UserSubscriptionActiveNotification;
use Packages\ShaunSocial\UserSubscription\Notification\UserSubscriptionRemindNotification;
use Packages\ShaunSocial\UserSubscription\Notification\UserSubscriptionStopNotification;

class SubscriptionTypeUserSubscription extends SubscriptionBase
{
    public function getDescription($subscription)
    {
        return '';
    }

    public function getWalletTypeExtra()
    {
        return 'user_subscription_buy';
    }
    
    public function getName($isDetail = false)
    {
        if (! $isDetail) {
            return __('Membership');
        }

        return '';
    }

    public function doActive($subscription)
    {
        $userSubscription = $subscription->getSubject();
        $user = $subscription->getUser();
        if ($userSubscription && $user) {
            $user->update([
                'role_id' => $userSubscription->role_id
            ]);

            //notify to user
            Notification::send($user, $user, UserSubscriptionActiveNotification::class, null, ['mail_many' => true], 'shaun_user_subscription');

            //send email to user
            Mail::send('user_subscription_active', $user, [
                'link' => route('web.user_subscription.index')
            ]);
        }
    }

    public function doRemind($subscription)
    {
        $userSubscription = $subscription->getSubject();
        $user = $subscription->getUser();
        if ($userSubscription && $user) {
            //notify to user
            Notification::send($user, $user, UserSubscriptionRemindNotification::class, null, ['params' => ['remind_day' => $subscription->remind_day]], 'shaun_user_subscription');

            //send email to user
            Mail::send('user_subscription_remind', $user, [
                'link' => route('web.user_subscription.index'),
                'date' => now()->addDays($subscription->remind_day)->setTimezone($user->timezone)->isoFormat(config('shaun_core.time_format.payment'))
            ]);
        }
    }

    public function doCancel($subscription)
    {
        $userSubscription = $subscription->getSubject();
        if ($userSubscription) {
            $userSubscription->update(['status' => UserSubscriptionStatus::CANCEL]);
        }
    }

    public function doStop($subscription, $force)
    {
        $userSubscription = $subscription->getSubject();
        $user = $subscription->getUser();
        if ($userSubscription && $user) {
            $userSubscription->update(['status' => UserSubscriptionStatus::STOP]);

            $role = Role::findByField('id', $userSubscription->expire_role_id);
            $roleId = $userSubscription->expire_role_id;
            if ($role->isDeleted()) {
                $roleDefault = Role::getDefault();
                $roleId = $roleDefault->id;
            }

            $user->update([
                'role_id' => $roleId
            ]);

            if (! $force) {
                //notify to user
                Notification::send($user, $user, UserSubscriptionStopNotification::class, null, [], 'shaun_user_subscription');

                //send email to user
                Mail::send('user_subscription_stop', $user, [
                    'link' => route('web.user_subscription.index')
                ]);
            }
        }
    }

    public function getRemindDay($subscription)
    {
        return setting('shaun_user_subscription.remind_day');
    }

    public function doResume($subscription)
    {
        $userSubscription = $subscription->getSubject();
        if ($userSubscription) {
            $userSubscription->update(['status' => UserSubscriptionStatus::ACTIVE]);
        }
    }
}
