<?php


namespace Packages\ShaunSocial\PaidContent\Wallet\PaidContent;

class PaidContentTransactionTypeBuyPost extends PaidContentTypeBase
{
    public function getDescription($transaction)
    {
        $subject = $transaction->getSubject();

        if ($transaction->amount > 0) {
            $user = $subject->getUser(true);

            return __('Receive funds from :1 for paid content', ['1' => $user->getTitle()]);
        }
        $user = $subject->getPostOwner();
        $user = getUserIncludeDelete($user);

        return __('Send funds to :1 to unlock paid content', ['1' => $user->getTitle()]);
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