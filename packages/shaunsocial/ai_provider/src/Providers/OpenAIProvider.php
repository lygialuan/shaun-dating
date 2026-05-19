<?php

namespace Packages\ShaunSocial\AiProvider\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OpenAIProvider extends AbstractAiProvider
{
    protected array $defaultConfig = [
        'api_key' => '',
        'model' => 'gpt-4o-mini',
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
        return 'openai';
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function sendMessage(string $message, array $config, array $context = []): array
    {
        if (! $this->validateMessage($message)) {
            return $this->errorResponse(__('Invalid message format or length.'));
        }

        if ($this->isModerationModel($config)) {
            return $this->performModerationRequest($message, $config);
        }

        return $this->performChatRequest($this->buildMessages($message, $config, $context), $config);
    }

    public function sendOnlyImage(string $imageEncoded, array $config, array $context = []): array
    {
        if ($imageEncoded === '') {
            return $this->errorResponse(__('Image payload is empty.'));
        }

        if ($this->isModerationModel($config)) {
            return $this->performModerationRequest('', $config, [
                [
                    'base64' => $imageEncoded,
                    'mime' => $context['mime_type'] ?? null,
                ],
            ]);
        }

        $prompt = $config['image_prompt'] ?? __('Describe the provided image.');

        $messages = [];
        if (! empty($config['system_prompt'])) {
            $messages[] = [
                'role' => 'system',
                'content' => $config['system_prompt'],
            ];
        }

        $mime = $context['mime_type'] ?? 'image/png';

        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $prompt,
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $this->buildDataUri($imageEncoded, $mime),
                    ],
                ],
            ],
        ];

        return $this->performChatRequest($messages, $config);
    }

    public function sendTextAndImage(string $message, string $imageEncoded, array $config, array $context = []): array
    {
        if (! $this->validateMessage($message)) {
            return $this->errorResponse(__('Invalid message format or length.'));
        }

        if ($imageEncoded === '') {
            return $this->errorResponse(__('Image payload is empty.'));
        }

        if ($this->isModerationModel($config)) {
            return $this->performModerationRequest($message, $config, [
                [
                    'base64' => $imageEncoded,
                    'mime' => $context['mime_type'] ?? null,
                ],
            ]);
        }

        $messages = [];
        if (! empty($config['system_prompt'])) {
            $messages[] = [
                'role' => 'system',
                'content' => $config['system_prompt'],
            ];
        }

        $mime = $context['mime_type'] ?? 'image/png';

        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $this->buildDataUri($imageEncoded, $mime),
                    ],
                ],
            ],
        ];

        return $this->performChatRequest($messages, $config);
    }

    public function sendOnlyVideo(string $videoEncoded, array $config, array $context = []): array
    {
        return $this->errorResponse(__('OpenAI provider does not yet support video-only requests.'));
    }

    /**
     * Execute chat request.
     *
     * @param array<int, mixed> $messages
     * @return array{status: bool, message: string, data?: array}
     */
    protected function performChatRequest(array $messages, array $config): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$config['api_key'],
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $config['model'] ?? $this->defaultConfig['model'],
                'messages' => $messages,
                'temperature' => (float) ($config['temperature'] ?? $this->defaultConfig['temperature']),
                //'max_tokens' => (int) ($config['max_tokens'] ?? $this->defaultConfig['max_tokens']),
                'max_completion_tokens' => (int) ($config['max_tokens'] ?? $this->defaultConfig['max_tokens']),
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
            Log::error('OpenAI API Error', [
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
            Log::error('OpenAI Provider Error', [
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

    protected function buildDataUri(string $base64, string $mime): string
    {
        $mime = $mime !== '' ? $mime : 'application/octet-stream';

        return sprintf('data:%s;base64,%s', $mime, $base64);
    }

    protected function isModerationModel(array $config): bool
    {
        $model = strtolower((string) ($config['model'] ?? $this->defaultConfig['model']));

        if ($model === '') {
            return false;
        }

        return Str::contains($model, 'moderation');
    }

    protected function performModerationRequest(string $textInput, array $config, array $imageInputs = []): array
    {
        try {
            $inputBlocks = $this->buildModerationInputBlocks($textInput, $imageInputs);

            if (empty($inputBlocks)) {
                $inputBlocks[] = [
                    'type' => 'text',
                    'text' => '',
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$config['api_key'],
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/moderations', [
                'model' => $config['model'] ?? 'omni-moderation-latest',
                'input' => $inputBlocks,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = $data['results'][0] ?? [];
                $categories = $result['categories'] ?? [];
                $flaggedCategories = [];

                if (is_array($categories)) {
                    foreach ($categories as $category => $flagged) {
                        if ($flagged) {
                            $flaggedCategories[] = $category;
                        }
                    }
                }

                $flagged = (bool) ($result['flagged'] ?? false);
                $summary = $flagged
                    ? __('Flagged categories: :list', ['list' => implode(', ', $flaggedCategories)])
                    : __('No policy violations detected.');

                return $this->successResponse(__('Moderation request completed.'), [
                    'flagged' => $flagged,
                    'reasons' => $flaggedCategories,
                    'summary' => $summary,
                    'response' => json_encode($data),
                    'details' => [
                        'results' => $result,
                        'usage' => $data['usage'] ?? null,
                        'id' => $data['id'] ?? null,
                        'model' => $data['model'] ?? null,
                    ],
                ]);
            }

            $error = $response->json();
            Log::error('OpenAI Moderation API Error', [
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
            Log::error('OpenAI Moderation Provider Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($this->providerErrorMessage($e->getMessage()));
        }
    }

    /**
     * @param array<int, array{base64: string, mime?: string|null}> $imageInputs
     * @return array<int, array<string, mixed>>
     */
    protected function buildModerationInputBlocks(string $textInput, array $imageInputs): array
    {
        $blocks = [];

        $textInput = trim($textInput);
        if ($textInput !== '') {
            $blocks[] = [
                'type' => 'text',
                'text' => $textInput,
            ];
        }

        foreach ($imageInputs as $image) {
            $base64 = $image['base64'] ?? '';
            if ($base64 === '') {
                continue;
            }

            $mime = $image['mime'] ?? 'image/png';
            $blocks[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $this->buildDataUri($base64, $mime),
                ],
            ];
        }

        return $blocks;
    }
}
