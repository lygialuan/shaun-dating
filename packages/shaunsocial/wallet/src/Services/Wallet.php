<?php


namespace Packages\ShaunSocial\Wallet\Services;

use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Wallet\Enum\WalletNotifyBalanceType;
use Packages\ShaunSocial\Wallet\Models\WalletNotifyBalance;
use Packages\ShaunSocial\Wallet\Models\WalletTransaction;

class Wallet
{
    public function add($type ,$userId, $amount, $subject, $typeExtra = '', $userFromId = 0, $params = [])
    {
        $lock = Cache::lock('user_add_wallet_'.$userId, config('shaun_core.cache.time.lock'));
        $result = ['status' => false];
        if ($lock->get()) {
            //must check amount
            if ($amount < 0 && $userId != config('shaun_wallet.system_wallet_user_id')) {                
                $currentBalance = WalletTransaction::getBalanceByUser($userId, false);
                if (abs($amount) > $currentBalance) {
                    return $result;
                }
            }

            if ($amount > 0 && $userId != config('shaun_wallet.system_wallet_user_id')) {
                WalletNotifyBalance::createRow($userId, WalletNotifyBalanceType::ADD);
            }

            if ($amount > 0 && ! empty($params['fee'])) {
                $amount = $amount - $params['fee'];
            }

            $transaction = WalletTransaction::create([
                'user_id' => $userId,
                'type' => $type,
                'type_extra' => $typeExtra,
                'amount' => $amount,
                'is_active' => true,
                'subject_type' => $subject->getSubjectType(),
                'subject_id' => $subject->id,
                'from_user_id' => $userFromId,
                'params' => json_encode($params)
            ]);

            $lock->release();
            
            return [
                'status' => true,
                'transaction' => $transaction
            ];
        }
        return $result;
    }

    public function transfer($type, $userFromId, $userToId, $amount, $subjectFrom, $subjectTo, $typeExtra = '', $params = [])
    {
        $result = ['status' => false];

        $ownerResult = $this->add($type, $userFromId, - $amount, $subjectFrom, $typeExtra, $userToId, $params);
        $userResult = $this->add($type, $userToId, $amount, $subjectTo, $typeExtra, $userFromId, $params);

        if ($ownerResult['status'] && $userResult['status']) {
            $result = [
                'status' => true,
                'from_transaction' => $ownerResult['transaction'],
                'to_transaction' => $userResult['transaction'],
            ];
        }

        return $result;
    }

    public function transferSubscription($subscription)
    {
        if (! $subscription->getClass()->hasFee()) {
            return $this->transfer($subscription->getClass()->getWalletType(), $subscription->user_id, config('shaun_wallet.system_wallet_user_id'), $subscription->amount, $subscription, $subscription, $subscription->getClass()->getWalletTypeExtra());
        } else {
            return $subscription->getClass()->handlePayment($subscription);
        }
    }

    public function buyItemFromSite($item)
    {
        return $this->transfer('payment', $item->user_id, config('shaun_wallet.system_wallet_user_id'),$item->getAmount(), $item, $item, $item->getWalletTypeExtra());
    }

    public function refundFromSite($item)
    {
        return $this->transfer('payment', config('shaun_wallet.system_wallet_user_id'), $item->user_id, $item->getRefundAmount(), $item, $item, $item->getWalletTypeExtraRefund());
    }
}
