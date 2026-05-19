<?php

namespace Packages\ShaunSocial\AiProvider\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider extends AbstractAiProvider
{
    protected array $defaultConfig = [
        'api_key' => '',
        'model' => 'claude-3-sonnet-20240229',
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
        return 'claude';
    }

    public function getName(): string
    {
        return 'Claude';
    }

    public function sendMessage(string $message, array $config, array $context = []): array
    {
        if (! $this->validateMessage($message)) {
            return $this->errorResponse(__('Invalid message format or length.'));
        }

        return $this->performClaudeRequest([
            'model' => $config['model'] ?? $this->defaultConfig['model'],
            'max_tokens' => (int) ($config['max_tokens'] ?? $this->defaultConfig['max_tokens']),
            'temperature' => (float) ($config['temperature'] ?? $this->defaultConfig['temperature']),
            'messages' => $this->buildMessages($message, $context),
            'system' => $config['system_prompt'] ?? $this->defaultConfig['system_prompt'],
        ], $config);
    }

    public function sendOnlyImage(string $imageEncoded, array $config, array $context = []): array
    {
        if ($imageEncoded === '') {
            return $this->errorResponse(__('Image payload is empty.'));
        }

        $prompt = $config['image_prompt'] ?? __('Describe the provided image.');

        $message = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $prompt,
                    ],
                    $this->buildImageContent($imageEncoded, $config),
                ],
            ],
        ];

        return $this->performClaudeRequest([
            'model' => $config['model'] ?? $this->defaultConfig['model'],
            'max_tokens' => (int) ($config['max_tokens'] ?? $this->defaultConfig['max_tokens']),
            'temperature' => (float) ($config['temperature'] ?? $this->defaultConfig['temperature']),
            'messages' => $message,
            'system' => $config['system_prompt'] ?? $this->defaultConfig['system_prompt'],
        ], $config);
    }

    public function sendTextAndImage(string $message, string $imageEncoded, array $config, array $context = []): array
    {
        if (! $this->validateMessage($message)) {
            return $this->errorResponse(__('Invalid message format or length.'));
        }

        if ($imageEncoded === '') {
            return $this->errorResponse(__('Image payload is empty.'));
        }

        $messages = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                    $this->buildImageContent($imageEncoded, $config),
                ],
            ],
        ];

        return $this->performClaudeRequest([
            'model' => $config['model'] ?? $this->defaultConfig['model'],
            'max_tokens' => (int) ($config['max_tokens'] ?? $this->defaultConfig['max_tokens']),
            'temperature' => (float) ($config['temperature'] ?? $this->defaultConfig['temperature']),
            'messages' => $messages,
            'system' => $config['system_prompt'] ?? $this->defaultConfig['system_prompt'],
        ], $config);
    }

    public function sendOnlyVideo(string $videoEncoded, array $config, array $context = []): array
    {
        return $this->errorResponse(__('Claude provider does not yet support video-only requests.'));
    }

    /**
     * Execute Claude API request.
     *
     * @param array<string, mixed> $payload
     * @return array{status: bool, message: string, data?: array}
     */
    protected function performClaudeRequest(array $payload, array $config): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $config['api_key'],
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['content'][0]['text'] ?? '';

                return $this->successResponse('Message sent successfully', [
                    'response' => $content,
                    'usage' => $data['usage'] ?? [],
                    'model' => $data['model'] ?? '',
                ]);
            }

            $error = $response->json();
            Log::error('Claude API Error', [
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
            Log::error('Claude Provider Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($this->providerErrorMessage($e->getMessage()));
        }
    }

    /**
     * @param array<string, mixed> $context
     * @return array<int, array<string, string>>
     */
    protected function buildMessages(string $message, array $context): array
    {
        $messages = [];

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

    /**
     * Build image content part for Claude request.
     *
     * @return array<string, mixed>
     */
    protected function buildImageContent(string $imageEncoded, array $config): array
    {
        $mediaType = $config['image_mime_type'] ?? 'image/jpeg';

        return [
            'type' => 'image',
            'source' => [
                'type' => 'base64',
                'media_type' => $mediaType,
                'data' => $imageEncoded,
            ],
        ];
    }
}
