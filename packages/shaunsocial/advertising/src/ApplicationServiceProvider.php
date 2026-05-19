<?php


namespace Packages\ShaunSocial\Advertising;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\Advertising\Console\Commands\AdvertisingReportRun;

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
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_advertising');

        //add router
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        //add migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //add view
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_advertising');
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
            AdvertisingReportRun::class,
        ]);
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('shaun_advertising:report_run')->withoutOverlapping(10)->everyMinute();
        });

        if (! alreadyInstalled()) {
            return;
        }
    }
}
