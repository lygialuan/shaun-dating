<?php

namespace Packages\ShaunSocial\AiProvider\Providers;

use Illuminate\Support\ServiceProvider;

class AiProviderServiceProvider extends ServiceProvider
{
    /**
     * Register bindings for AI providers.
     */
    public function register(): void
    {
        $this->app->bind('ai.provider.openai', fn () => new OpenAIProvider());
        $this->app->bind('ai.provider.claude', fn () => new ClaudeProvider());
        $this->app->bind('ai.provider.gemini', fn () => new GeminiProvider());
        $this->app->bind('ai.provider.grok', fn () => new GrokProvider());
        $this->app->bind('ai.provider.perplexity', fn () => new PerplexityProvider());
    }

    public function boot(): void
    {
        //
    }
}
