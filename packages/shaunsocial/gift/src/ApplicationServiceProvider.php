<?php

namespace Packages\ShaunSocial\Gift;

use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\Gift\Services\GiftService;

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
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_gift');

        //add router
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        //add migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //add view
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_gift');


        $this->app->bind('gift', function ($app) {
            return $app->make(GiftService::class);
        });
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
    }
}
