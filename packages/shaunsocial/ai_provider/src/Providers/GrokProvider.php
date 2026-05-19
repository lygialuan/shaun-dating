<?php

namespace Packages\ShaunSocial\AiProvider\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GrokProvider extends AbstractAiProvider
{
    protected array $defaultConfig = [
        'api_key' => '',
        'model' => 'grok-beta',
        'temperature' => 0.7,
        'max_tokens' => 1000,
        'system_prompt' => 'You are a helpful AI assistant.',
    ];

    protected array $requiredFields = [
        'api_key',
        'model',
        'temperature',
        'max_tokens',
        'system_prompt',
    ];

    public function getKey(): string
    {
        return 'grok';
    }

    public function getName(): string
    {
        return 'Grok';
    }

    public function sendMessage(string $message, array $config, array $context = []): array
    {
        if (! $this->validateMessage($message)) {
            return $this->errorResponse(__('Invalid message format or length.'));
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$config['api_key'],
                'Content-Type' => 'application/json',
            ])->post('https://api.x.ai/v1/chat/completions', [
                'model' => $config['model'] ?? $this->defaultConfig['model'],
                'messages' => $this->buildMessages($message, $config, $context),
                'temperature' => (float) ($config['temperature'] ?? $this->defaultConfig['temperature']),
                'max_tokens' => (int) ($config['max_tokens'] ?? $this->defaultConfig['max_tokens']),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';

                return $this->successResponse('Message sent successfully', [
                    'response' => $content,
                    'usage' => $data['usage'] ?? [],
                    'model' => $data['model'] ?? '',
                ]);
            }

            $error = $response->json();
            Log::error('Grok API Error', [
                'response' => $error,
                'status' => $response->status(),
            ]);

            $errorDetail = $error['error']['message'] ?? null;
            if (! $errorDetail) {
                $errorDetail = $response->body();
            }
            if (! $errorDetail && $error !== null) {
                $errorDetail = json_encode($error);
            }

            return $this->errorResponse($this->providerErrorMessage($errorDetail));
        } catch (\Exception $e) {
            Log::error('Grok Provider Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($this->providerErrorMessage($e->getMessage()));
        }
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $context
     * @return array<int, array<string, string>>
     */
    protected function buildMessages(string $message, array $config, array $context): array
    {
        $messages = [];

        if (! empty($config['system_prompt'])) {
            $messages[] = [
                'role' => 'system',
                'content' => $config['system_prompt'],
            ];
        }

        if (! empty($context['messages'])) {
            foreach ($context['messages'] as $contextMessage) {
                $messages[] = [
                    'role' => $contextMessage['role'] ?? 'user',
                    'content' => $contextMessage['content'] ?? '',
                ];
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $messages;
    }
}
