<?php

namespace Packages\ShaunSocial\Group;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\Group\Console\Commands\GroupCronRun;
use Packages\ShaunSocial\Group\Console\Commands\GroupPostPendingNotify;

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
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_group');

        //add router
        foreach (glob(__DIR__.'/../routes/*.php') as $file) {
            $this->loadRoutesFrom($file);
        }

        //add migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //add view
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_group');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! alreadyInstalled()) {
            return;
        }

        // command
        $this->commands([
            GroupPostPendingNotify::class,
            GroupCronRun::class
        ]);

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('shaun_group:group_post_pending_notify')->withoutOverlapping(10)->everyMinute();
            $schedule->command('shaun_group:group_cron_run')->withoutOverlapping(10)->everyMinute();
        });
    }
}
