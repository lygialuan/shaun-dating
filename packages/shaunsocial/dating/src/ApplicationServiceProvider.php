<?php

namespace Packages\ShaunSocial\Dating;

use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\Dating\Services\DatingService;
use Packages\ShaunSocial\Dating\Console\Commands\DatingReminderAdminReviewPhotos;
use Illuminate\Console\Scheduling\Schedule;

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
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_dating');

        //add router
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        //add migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //add view
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_dating');

        $this->app->bind('dating', function ($app) {
            return $app->make(DatingService::class);
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
            DatingReminderAdminReviewPhotos::class,
        ]);

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('shaun_dating:reminder_admin_review_photos')->withoutOverlapping(10)->dailyAt('06:00');
        });

        if (! alreadyInstalled()) {
            return;
        }
    }
}
