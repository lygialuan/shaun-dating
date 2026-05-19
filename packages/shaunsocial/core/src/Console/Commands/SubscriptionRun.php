<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\Core\Enum\SubscriptionStatus;
use Packages\ShaunSocial\Core\Models\Subscription;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;

class SubscriptionRun extends Command
{   
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:subscription_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscription run task.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //subscription remind
        $subscriptions = Subscription::where('reminded_at', '<', now())->where('reminded', false)->where('status', SubscriptionStatus::ACTIVE)->limit(setting('feature.item_per_page'))->get();
        $subscriptions->each(function($subscription) {
            Log::channel('shaun_subscription')->info('Remind');
            Log::channel('shaun_subscription')->info(print_r($subscription->attributesToArray(),true));

            $subscription->doRemind();
        });

        //stop when cancel
        $subscriptions = Subscription::where('expired_at', '<', now())->where('status', SubscriptionStatus::CANCEL)->limit(setting('feature.item_per_page'))->get();
        $subscriptions->each(function($subscription) {
            Log::channel('shaun_subscription')->info('Cancel');
            Log::channel('shaun_subscription')->info(print_r($subscription->attributesToArray(),true));
            
            $subscription->doStop();
        });
        
        //subscription expire
        $subscriptions = Subscription::where('expired_at', '<', now())->where('status', SubscriptionStatus::ACTIVE)->limit(setting('feature.item_per_page'))->get();
        $subscriptions->each(function($subscription) {
            Log::channel('shaun_subscription')->info('Expire');
            Log::channel('shaun_subscription')->info(print_r($subscription->attributesToArray(),true));
            
            DB::beginTransaction();
            try {
                if ($subscription->canContinue()) {
                    if ($subscription->gateway_id == config('shaun_gateway.wallet_id')) {  
                        $userResult = Wallet::transferSubscription($subscription);
                        if ($userResult['status']) {
                            $transaction = $userResult['from_transaction'];
                            $subscription->doActive([], true, $subscription->amount, $transaction->getId());
                            DB::commit();
                            return;
                        } else {
                            DB::rollBack();
                        }
                    }
                }

                $subscription->doStop();
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
            }
        });
    }
}
