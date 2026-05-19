<?php

namespace Packages\ShaunSocial\AiChatProfiles;

use Illuminate\Support\ServiceProvider;
use Packages\ShaunSocial\AiChatProfiles\Contracts\AiProviderInterface;
use Packages\ShaunSocial\AiChatProfiles\Observers\AiChatMessageObserver;
use Packages\ShaunSocial\AiChatProfiles\Services\AiChatJobDispatcher;
use Packages\ShaunSocial\AiChatProfiles\Support\AiPromptBuilder;
use Packages\ShaunSocial\AiChatProfiles\Support\AiProviderManager;
use Packages\ShaunSocial\Chat\Models\ChatMessage;

class ApplicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_ai_chat_profiles');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_ai_chat_profiles');

        $this->app->singleton(AiProviderManager::class, function ($app) {
            return new AiProviderManager($app);
        });

        $this->app->bind(AiProviderInterface::class, function ($app) {
            return $app->make(AiProviderManager::class)->driver();
        });

        $this->app->singleton(AiPromptBuilder::class);

        $this->app->singleton(AiChatJobDispatcher::class);
    }

    public function boot(): void
    {
        if (! function_exists('alreadyInstalled') || ! alreadyInstalled()) {
            return;
        }

        ChatMessage::observe($this->app->make(AiChatMessageObserver::class));
    }
}
