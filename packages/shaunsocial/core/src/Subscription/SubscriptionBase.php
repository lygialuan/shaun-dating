<?php


namespace Packages\ShaunSocial\Core\Subscription;

abstract class SubscriptionBase
{
    abstract public function getDescription($subscription);

    public function getWalletType()
    {
        return 'payment';
    }

    public function hasFee()
    {
        return false;
    }

    public function handlePayment($subscription)
    {
        
    }

    public function getCustomName($subscription)
    {
        return '';
    }

    public function canContinue($subscription)
    {
        if ($subscription->getUser()) {
            return true;
        }
        
        return false;
    }

    abstract public function getWalletTypeExtra();
    abstract public function getName($isDetail = false);
    abstract public function doActive($subscription);
    abstract public function doRemind($subscription);
    abstract public function getRemindDay($subscription);
    abstract public function doCancel($subscription);
    abstract public function doStop($subscription, $force);
    abstract public function doResume($subscription);
}
