<?php

use Packages\ShaunSocial\Core\Models\User;

if ( ! function_exists('getAmountOfPaidContent')) {
    function getAmountOfPaidContent($amount, $userIds = [], $refCode = '')
    {
        $fee = round($amount * setting('shaun_paid_content.commission_fee') / 100, 2);
        if ($fee < 0) {
            $fee = 0;
        }

        $amount = $amount - $fee;
        $amountRef = round($fee * setting('shaun_paid_content.commission_referral') / 100, 2);
        if ($amountRef < 0) {
            $amountRef = 0;
        }

        $userRef = null;
        $amountRefReal = 0;
        if ($refCode) {
            $userRef = User::findByField('ref_code', $refCode);
            if ($userRef && !in_array($userRef->id,$userIds)) {
                $fee = $fee - $amountRef;
                $amountRefReal = $amountRef;
            }
        }

        return [$amount, $fee, $amountRefReal];
    }
}