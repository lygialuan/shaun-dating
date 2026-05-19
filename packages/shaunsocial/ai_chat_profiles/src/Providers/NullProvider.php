<?php

namespace Packages\ShaunSocial\AiChatProfiles\Providers;

use Packages\ShaunSocial\AiChatProfiles\Contracts\AiProviderInterface;
use Packages\ShaunSocial\AiChatProfiles\DataObjects\AiChatRequest;
use Packages\ShaunSocial\AiChatProfiles\DataObjects\AiChatResponse;

class NullProvider implements AiProviderInterface
{
    public function name(): string
    {
        return 'null';
    }

    public function chat(AiChatRequest $request): AiChatResponse
    {
        return new AiChatResponse(
            content: '',
            provider: $this->name(),
            model: $request->model ?? 'null',
            tokensPrompt: 0,
            tokensCompletion: 0,
            latencyMs: 0,
            flagged: false,
            flagReason: null,
            finishReason: 'noop',
            raw: [],
        );
    }
}
