<?php

namespace Packages\ShaunSocial\AiChatProfiles\Providers;

use Packages\ShaunSocial\AiChatProfiles\Contracts\AiProviderInterface;
use Packages\ShaunSocial\AiChatProfiles\DataObjects\AiChatRequest;
use Packages\ShaunSocial\AiChatProfiles\DataObjects\AiChatResponse;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatRole;
use Packages\ShaunSocial\AiChatProfiles\Exceptions\AiProviderException;
use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;
use Packages\ShaunSocial\AiProvider\Providers\AiProviderInterface as DelegateInterface;
use Packages\ShaunSocial\AiProvider\Traits\ManagesAiProviderKeyStatus;

class AiProviderKeyBridge implements AiProviderInterface
{
    use ManagesAiProviderKeyStatus;

    public function __construct(
        private AiProviderKey $key,
        private DelegateInterface $delegate,
    ) {
    }

    public function name(): string
    {
        return $this->key->provider->name ?? 'unknown';
    }

    public function chat(AiChatRequest $request): AiChatResponse
    {
        $messages = $request->messages;

        // First system message
        $systemMessage = null;
        $remaining = [];
        foreach ($messages as $message) {
            if ($systemMessage === null && $message->role === AiChatRole::SYSTEM) {
                $systemMessage = $message;
            } else {
                $remaining[] = $message;
            }
        }

        // Last user message is the one passed to sendMessage; everything before it is context
        $lastMessage = null;
        $middleMessages = [];
        for ($i = count($remaining) - 1; $i >= 0; $i--) {
            if ($lastMessage === null && $remaining[$i]->role === AiChatRole::USER) {
                $lastMessage = $remaining[$i];
                $middleMessages = array_slice($remaining, 0, $i);
                break;
            }
        }

        // If no user message was found, use the last message regardless of role
        if ($lastMessage === null && $remaining !== []) {
            $lastMessage = array_pop($remaining);
            $middleMessages = $remaining;
        }

        $config = (array) ($this->key->config ?? []);
        if ($systemMessage !== null) {
            $config['system_prompt'] = $systemMessage->content;
        }

        $context = [
            'messages' => array_map(
                static fn ($m) => ['role' => $m->role->value, 'content' => $m->content],
                $middleMessages,
            ),
        ];

        $start = hrtime(true);

        try {
            $result = $this->delegate->sendMessage(
                $lastMessage !== null ? $lastMessage->content : '',
                $config,
                $context,
            );
        } catch (\Throwable $e) {
            $this->handleAiProviderKeyFailure($this->key, $e->getMessage());
            throw AiProviderException::transport($this->name(), $e->getMessage(), $e);
        }

        $latencyMs = (int) ((hrtime(true) - $start) / 1_000_000);

        if (! ($result['status'] ?? false)) {
            $errorMessage = $result['message'] ?? 'Provider returned a failure response.';
            $this->handleAiProviderKeyFailure($this->key, $errorMessage);
            throw AiProviderException::invalidResponse($this->name(), $errorMessage);
        }

        $this->handleAiProviderKeySuccess($this->key);

        $data = $result['data'] ?? [];
        $usage = $data['usage'] ?? [];

        return new AiChatResponse(
            content: (string) ($data['response'] ?? ''),
            provider: $this->name(),
            model: (string) ($data['model'] ?? $request->model ?? ''),
            tokensPrompt: (int) ($usage['prompt_tokens'] ?? 0),
            tokensCompletion: (int) ($usage['completion_tokens'] ?? 0),
            latencyMs: $latencyMs,
            flagged: false,
            flagReason: null,
            finishReason: null,
            raw: $data,
        );
    }
}
