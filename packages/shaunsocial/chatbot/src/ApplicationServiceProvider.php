<?php

namespace Packages\ShaunSocial\Chatbot;

use Illuminate\Support\ServiceProvider;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // add helpers
        foreach (glob(__DIR__.'/Helpers/*.php') as $file) {
            require_once $file;
        }

        // add config
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_chatbot');

        // add routes
        foreach (glob(__DIR__.'/../routes/*.php') as $file) {
            $this->loadRoutesFrom($file);
        }

        // add migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // add views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_chatbot');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! alreadyInstalled()) {
            return;
        }
    }
}
