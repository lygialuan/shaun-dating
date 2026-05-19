<?php

namespace Packages\ShaunSocial\UserPage;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\UserPage\Console\Commands\UserPageNotificationCronRun;
use Packages\ShaunSocial\UserPage\Console\Commands\UserPageReportUpdate;
use Packages\ShaunSocial\UserPage\Console\Commands\UserPageCreateSubProfileRun;

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
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_user_page');

        //add router
        foreach (glob(__DIR__.'/../routes/*.php') as $file) {
            $this->loadRoutesFrom($file);
        }

        //add migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //add view
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_user_page');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->request->headers->get('SupportCookie')) {
            $route = Route::getRoutes()->match($this->app->request);
            $arrayRouter = ['user_page_switch_page', 'user_page_login_back', 'user_page_transfer_owner', 'user_page_remove_admin', 'user_page_delete'];
            if ($route && in_array($route->action['as'], $arrayRouter)) {
                app('router')->pushMiddlewareToGroup('api', \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
            }
        }

        // command
        $this->commands([
            UserPageReportUpdate::class,
            UserPageNotificationCronRun::class,
            UserPageCreateSubProfileRun::class
        ]);
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('shaun_user_page:report_update')->withoutOverlapping(10)->everyMinute();
            $schedule->command('shaun_user_page:notification_cron_run')->withoutOverlapping(10)->everyMinute();
            $schedule->command('shaun_user_page:create_sub_profiles')->withoutOverlapping(10)->everyMinute();
        });

        if (! alreadyInstalled()) {
            return;
        }
    }
}
