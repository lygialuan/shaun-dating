<?php

namespace Packages\ShaunSocial\AiProvider;

use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\AiProvider\Providers\AiProviderServiceProvider;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(AiProviderServiceProvider::class);

        // Include package helper functions if present
        foreach (glob(__DIR__.'/Helpers/*.php') as $file) {
            require_once $file;
        }

        // Merge package configuration
        if (file_exists(__DIR__.'/../config/constant.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_ai_provider');
        }

        // Register routes
        foreach (glob(__DIR__.'/../routes/*.php') as $file) {
            $this->loadRoutesFrom($file);
        }

        // Register migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_ai_provider');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! function_exists('alreadyInstalled') || ! alreadyInstalled()) {
            return;
        }
    }
}
