<?php

namespace Packages\ShaunSocial\AiProvider\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider extends AbstractAiProvider
{
    protected array $defaultConfig = [
        'api_key' => '',
        'model' => 'gemini-1.5-pro',
        'temperature' => 0.7,
        'max_output_tokens' => 1000,
        'system_prompt' => 'You are a helpful AI assistant.',
    ];

    protected array $requiredFields = [
        'api_key',
        'model',
        'temperature',
        'max_output_tokens',
        'system_prompt',
    ];

    public function getKey(): string
    {
        return 'gemini';
    }

    public function getName(): string
    {
        return 'Gemini';
    }

    public function sendMessage(string $message, array $config, array $context = []): array
    {
        if (! $this->validateMessage($message)) {
            return $this->errorResponse(__('Invalid message format or length.'));
        }

        return $this->performGeminiRequest(
            $this->buildContents($message, $config, $context),
            $config
        );
    }

    public function sendOnlyImage(string $imageEncoded, array $config, array $context = []): array
    {
        if ($imageEncoded === '') {
            return $this->errorResponse(__('Image payload is empty.'));
        }

        $prompt = $config['image_prompt'] ?? __('Describe the provided image.');

        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                    $this->buildImagePart($imageEncoded, $config),
                ],
            ],
        ];

        return $this->performGeminiRequest($contents, $config);
    }

    public function sendTextAndImage(string $message, string $imageEncoded, array $config, array $context = []): array
    {
        if (! $this->validateMessage($message)) {
            return $this->errorResponse(__('Invalid message format or length.'));
        }

        if ($imageEncoded === '') {
            return $this->errorResponse(__('Image payload is empty.'));
        }

        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $message],
                    $this->buildImagePart($imageEncoded, $config),
                ],
            ],
        ];

        return $this->performGeminiRequest($contents, $config);
    }

    public function sendOnlyVideo(string $videoEncoded, array $config, array $context = []): array
    {
        return $this->errorResponse(__('Gemini provider does not yet support video requests.'));
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $context
     * @return array<int, array<string, mixed>>
     */
    protected function buildContents(string $message, array $config, array $context): array
    {
        $contents = [];

        if (! empty($config['system_prompt'])) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $config['system_prompt']],
                ],
            ];
        }

        if (! empty($context['messages'])) {
            foreach ($context['messages'] as $contextMessage) {
                $contextRole = $contextMessage['role'] ?? 'user';
                if ($contextRole === 'assistant') {
                    $contextRole = 'model';
                }

                if (! in_array($contextRole, ['user', 'model'], true)) {
                    $contextRole = 'user';
                }

                $contents[] = [
                    'role' => $contextRole,
                    'parts' => [
                        ['text' => $contextMessage['content'] ?? ''],
                    ],
                ];
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $message],
            ],
        ];

        return $contents;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function extractResponseText(array $data): string
    {
        $candidates = $data['candidates'] ?? [];
        $firstCandidate = $candidates[0] ?? null;
        if (! $firstCandidate) {
            return __('I didn\'t receive any reply. Could you ask again?');
        }

        $parts = $firstCandidate['content']['parts'] ?? [];
        $texts = [];
        foreach ($parts as $part) {
            $text = trim((string) ($part['text'] ?? ''));
            if ($text !== '') {
                $texts[] = $text;
            }
        }

        if (! empty($texts)) {
            return implode("\n\n", $texts);
        }

        $finishReason = $firstCandidate['finishReason'] ?? '';
        if ($finishReason === 'SAFETY') {
            return __('The assistant held back the answer for safety reasons.');
        }

        if ($finishReason === 'MAX_TOKENS') {
            return __('The assistant hit its response limit. Try a shorter question.');
        }

        $blockReason = $data['promptFeedback']['blockReason'] ?? null;
        if ($blockReason) {
            $readableReason = strtolower(str_replace('_', ' ', $blockReason));

            return __('The assistant couldn\'t answer because: :reason.', ['reason' => $readableReason]);
        }

        return __('I didn\'t receive any reply. Could you ask again?');
    }

    /**
     * Execute Gemini API request.
     *
     * @param array<int, mixed> $contents
     * @return array{status: bool, message: string, data?: array}
     */
    protected function performGeminiRequest(array $contents, array $config): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'.
                ($config['model'] ?? $this->defaultConfig['model']).
                ':generateContent?key='.
                $config['api_key'],
                [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => (float) ($config['temperature'] ?? $this->defaultConfig['temperature']),
                        'maxOutputTokens' => (int) ($config['max_output_tokens'] ?? $this->defaultConfig['max_output_tokens']),
                    ],
                ]
            );

            if ($response->successful()) {
                $data = $response->json();
                $content = $this->extractResponseText($data);

                return $this->successResponse(__('Here\'s what the assistant replied.'), [
                    'response' => $content,
                    'usage' => $data['usageMetadata'] ?? [],
                    'model' => $config['model'] ?? $this->defaultConfig['model'],
                ]);
            }

            $error = $response->json();
            Log::error('Gemini API Error', [
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
            Log::error('Gemini Provider Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($this->providerErrorMessage($e->getMessage()));
        }
    }

    /**
     * Build inline image part.
     *
     * @return array<string, mixed>
     */
    protected function buildImagePart(string $imageEncoded, array $config): array
    {
        $mimeType = $config['image_mime_type'] ?? 'image/jpeg';

        return [
            'inlineData' => [
                'mimeType' => $mimeType,
                'data' => $imageEncoded,
            ],
        ];
    }
}
