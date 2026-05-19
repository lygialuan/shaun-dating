<?php


namespace Packages\ShaunSocial\Core\Traits;

trait IsSubscriptionPackage
{
    public function initializeIsSubscriptionPackage()
    {
        $this->fillable[] = 'billing_cycle';
        $this->fillable[] = 'billing_cycle_type';
    }

    public function getBillingCycle()
    {
        return $this->billing_cycle;
    }

    public function getBillingCycleType()
    {
        return $this->billing_cycle_type->value;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTrialDay()
    {
        return 0;
    }

    public function getTrialAmount()
    {
        return 0;
    }
}
