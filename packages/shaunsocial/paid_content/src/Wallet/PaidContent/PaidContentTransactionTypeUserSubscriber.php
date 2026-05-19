<?php


namespace Packages\ShaunSocial\PaidContent\Wallet\PaidContent;

class PaidContentTransactionTypeUserSubscriber extends PaidContentTypeBase
{
    public function getDescription($transaction)
    {
        $subject = $transaction->getSubject();
        
        if ($transaction->amount > 0) {
            $user = $subject->getUser(true);

            return __('Receive funds from :1 for exclusive content subscription', ['1' =>  $user->getTitle()]);
        }

        $user = $subject->getSubject();
        $user = getUserIncludeDelete($user);

        return __('Send funds to :1 for exclusive content subscription', ['1' =>  $user->getTitle()]);
    }

    public function getGross($transaction)
    {
        $params = $transaction->getParams();
        
        if ($transaction->amount >= 0) {
            return formatNumber($transaction->amount + $params['fee']);
        } else {
            return formatNumber($transaction->amount);
        }
    }

    public function getNet($transaction)
    {
        return formatNumber($transaction->amount);
    }

    public function getFee($transaction)
    {
        $params = $transaction->getParams();

        if ($transaction->amount >= 0) {
            return formatNumber($params['fee']);
        } else {
            return 0;
        }
    }
}