<?php

namespace Packages\ShaunSocial\MigrateOldDating;

use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\MigrateOldDating\Console\Commands\SyncOldUserRun;
use Illuminate\Console\Scheduling\Schedule;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Include package helper functions if present
        foreach (glob(__DIR__.'/Helpers/*.php') as $file) {
            require_once $file;
        }

        // Merge package configuration
        if (file_exists(__DIR__.'/../config/constant.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_migrate_old_dating');
        }

        // Register routes
        foreach (glob(__DIR__.'/../routes/*.php') as $file) {
            $this->loadRoutesFrom($file);
        }

        // Register migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_migrate_old_dating');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([
            SyncOldUserRun::class,
        ]);

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('shaun_migrate_old_dating:sync_old_users')->withoutOverlapping(10)->everyMinute();
        });

        if (! alreadyInstalled()) {
            return;
        }
    }
}
