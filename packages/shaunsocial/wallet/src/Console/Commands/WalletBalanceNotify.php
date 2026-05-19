<?php


namespace Packages\ShaunSocial\Wallet\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Wallet\Enum\WalletNotifyBalanceType;
use Packages\ShaunSocial\Wallet\Models\WalletNotifyBalance;
use Packages\ShaunSocial\Wallet\Repositories\Api\WalletRepository;

class WalletBalanceNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_wallet:notify_balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify balance task.';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $walletRepository = null;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $balances = WalletNotifyBalance::limit(setting('feature.item_per_page'))->get();
        $balances->each(function($balance){
            $user = $balance->getUser();
            if ($user) {
                switch ($balance->type) {
                    case WalletNotifyBalanceType::ADD:
                        if ($user->getCurrentBalance() >= setting('shaun_wallet.amount_notify_balance')) {
                            $user->update(['wallet_notify_lower' => false]);
                        }
                        break;
                    case WalletNotifyBalanceType::REDUCE:
                        if (! $user->wallet_notify_lower && $user->getCurrentBalance() < setting('shaun_wallet.amount_notify_balance')) {
                            $user->update(['wallet_notify_lower' => true]);
                            $this->walletRepository->notify_balance($user);
                        }
                        break;
                }
            }

            $balance->delete();
        });
    }
}
