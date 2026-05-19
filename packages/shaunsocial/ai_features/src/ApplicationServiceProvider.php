<?php

namespace Packages\ShaunSocial\AiFeatures;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Packages\ShaunSocial\AiFeatures\Observers\CommentObserver;
use Packages\ShaunSocial\AiFeatures\Observers\CommentReplyObserver;
use Packages\ShaunSocial\AiFeatures\Observers\PostObserver;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureTaskManager;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureTaskScheduler;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ProcessorImageModeration;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ProcessorTextModeration;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ProcessorVideoModeration;
use Packages\ShaunSocial\AiFeatures\Console\Commands\ProcessAiFeatureTasks;
use Packages\ShaunSocial\Core\Models\Comment;
use Packages\ShaunSocial\Core\Models\CommentReply;
use Packages\ShaunSocial\Core\Models\Post;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        // include helpers if any are added later
        foreach (glob(__DIR__.'/Helpers/*.php') as $file) {
            require_once $file;
        }

        $this->mergeConfigFrom(__DIR__.'/../config/ai_features.php', 'shaun_ai_features');

        // register routes
        foreach (glob(__DIR__.'/../routes/*.php') as $file) {
            $this->loadRoutesFrom($file);
        }

        // register migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_ai_features');

        $this->app->singleton(AiFeatureTaskManager::class, function ($app) {
            return new AiFeatureTaskManager([
                $app->make(ProcessorTextModeration::class),
                $app->make(ProcessorImageModeration::class),
                $app->make(ProcessorVideoModeration::class),
            ]);
        });
        $this->app->singleton(AiFeatureTaskScheduler::class, fn ($app) => new AiFeatureTaskScheduler(
            $app->make(AiFeatureTaskManager::class)
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! function_exists('alreadyInstalled') || ! alreadyInstalled()) {
            return;
        }

        $this->commands([
            ProcessAiFeatureTasks::class,
        ]);
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('shaun_ai_feature:tasks')->withoutOverlapping(1)->everyMinute();
        });

        Post::observe(PostObserver::class);
        Comment::observe(CommentObserver::class);
        CommentReply::observe(CommentReplyObserver::class);
    }
}
