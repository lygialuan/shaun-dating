<?php

namespace Packages\ShaunSocial\Gateway;

use Illuminate\Support\ServiceProvider;
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
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_gateway');

        //add router
        foreach (glob(__DIR__.'/../routes/*.php') as $file) {
            $this->loadRoutesFrom($file);
        }

        //add migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //add view
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_gateway');
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
