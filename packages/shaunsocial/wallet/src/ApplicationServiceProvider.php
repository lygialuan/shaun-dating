<?php

namespace Packages\ShaunSocial\Wallet;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\Wallet\Console\Commands\WalletBalanceNotify;
use Packages\ShaunSocial\Wallet\Models\WalletTransaction;
use Packages\ShaunSocial\Wallet\Observers\WalletTransactionObserver;
use Packages\ShaunSocial\Wallet\Services\Wallet;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //add helpers
        foreach (glob(__DIR__.'/Helpers/*.php') as $file) {
            require_once $file;
        }
        
        //add config
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_wallet');

        //add router
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        //add migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //add view
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_wallet');

        $this->app->bind('wallet', function () {
            return new Wallet;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // command
        $this->commands([
            WalletBalanceNotify::class,
        ]);
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('shaun_wallet:notify_balance')->withoutOverlapping(10)->everyMinute();
        });

        if (! alreadyInstalled()) {
            return;
        }

        WalletTransaction::observe(WalletTransactionObserver::class);
    }
}
