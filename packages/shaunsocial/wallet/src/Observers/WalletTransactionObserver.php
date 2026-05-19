<?php
 

namespace Packages\ShaunSocial\Wallet\Observers;
 
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Wallet\Models\WalletTransaction;
use Packages\ShaunSocial\Wallet\Notification\WalletBalanceChangeNotification;
use Packages\ShaunSocial\Wallet\Notification\WalletCommissionNotification;

class WalletTransactionObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the User "created" event.
     */
    public function created(WalletTransaction $transaction): void
    {
        if ($transaction->user_id != config('shaun_wallet.system_wallet_user_id')) {
            if ($transaction->amount < 0) {
                Notification::send($transaction->getUser(),$transaction->getUser(), WalletBalanceChangeNotification::class, $transaction, ['is_system' => true], 'shaun_wallet', false, false);
            }
        }

        if ($transaction->type == 'commission') {
            Notification::send($transaction->getUser(),$transaction->getUser(), WalletCommissionNotification::class, $transaction, ['is_system' => true], 'shaun_wallet', false, false);
        }
    }
}