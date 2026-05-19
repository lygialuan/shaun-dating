<?php

namespace Packages\ShaunSocial\AiProvider\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;

trait ManagesAiProviderKeyStatus
{
    protected function handleAiProviderKeySuccess(AiProviderKey $key): void
    {
        if (($key->failure_count ?? 0) === 0 && $key->status === 'healthy' && ! $key->last_error_message) {
            return;
        }

        $key->failure_count = 0;
        $key->status = 'healthy';
        $key->last_error_message = null;
        $key->last_error_at = null;
        $key->save();
    }

    protected function handleAiProviderKeyFailure(AiProviderKey $key, string $message, int $threshold = 3, array $context = []): void
    {
        $message = trim($message) ?: __('Unknown provider error.');
        $key->failure_count = ($key->failure_count ?? 0) + 1;
        $key->last_error_message = Str::limit($message, 500);
        $key->last_error_at = now();

        $autoDisabled = false;
        if ($key->failure_count >= $threshold && $key->status !== 'error') {
            $key->status = 'error';
            $key->is_active = false;
            $autoDisabled = true;
        }

        $key->save();

        $logContext = array_merge($context,[
            'key_id' => $key->id,
            'provider' => $key->provider?->name,
            'failure_count' => $key->failure_count,
            'message' => $key->last_error_message,
        ]);

        Log::channel('ai_provider')->warning('AI provider key request failed', $logContext);

        if ($autoDisabled) {
            Log::channel('ai_provider')->error('AI provider key automatically disabled after consecutive failures', $logContext);
        }
    }
}
