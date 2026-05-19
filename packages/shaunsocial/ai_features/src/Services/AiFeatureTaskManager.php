<?php

namespace Packages\ShaunSocial\AiFeatures\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ContentTypeProcessorInterface;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ProcessorImageModeration;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ProcessorTextModeration;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ProcessorVideoModeration;
use Packages\ShaunSocial\AiProvider\Traits\ManagesAiProviderKeyStatus;
use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;
use Throwable;

class AiFeatureTaskManager
{
    use ManagesAiProviderKeyStatus;

    protected array $contentTypeSettingMap = [
        'text' => 'ai_features.text_provider_key_id',
        'image' => 'ai_features.image_provider_key_id',
        'video' => 'ai_features.video_provider_key_id',
    ];

    /**
     * @var ContentTypeProcessorInterface[]
     */
    protected array $contentProcessors = [];

    public function __construct(array $contentProcessors = [])
    {
        if (empty($contentProcessors)) {
            $contentProcessors = [
                new ProcessorTextModeration(),
                new ProcessorImageModeration(),
                new ProcessorVideoModeration(),
            ];
        }

        $this->contentProcessors = $contentProcessors;
    }

    /**
     * Create (or reuse) a moderation task.
     *
     * @param array<string, mixed> $attributes
     */
    public function createTask(array $attributes): AiFeatureTask
    {
        $defaults = [
            'status' => AiFeatureTask::STATUS_PENDING,
            'auto_action' => AiFeatureTask::AUTO_ACTION_NONE,
            'attempts' => 0,
            'max_attempts' => config('shaun_ai_features.tasks.max_attempts', 3),
            'next_run_at' => Carbon::now(),
        ];

        $payload = Arr::get($attributes, 'payload', []);
        if (! is_array($payload)) {
            $payload = [];
        }

        return AiFeatureTask::updateOrCreate(
            [
                'subject_type' => $attributes['subject_type'],
                'subject_id' => $attributes['subject_id'],
                'content_type' => $attributes['content_type'],
                'content_ref_type' => $attributes['content_ref_type'] ?? null,
                'content_ref_id' => $attributes['content_ref_id'] ?? null,
            ],
            array_merge(
                $defaults,
                Arr::except($attributes, ['subject_type', 'subject_id', 'content_type', 'content_ref_type', 'content_ref_id']),
                ['payload' => $payload]
            )
        );
    }

    /**
     * Process a task by calling the appropriate provider endpoint.
     *
     * @return array{status: bool, message: string, data?: array, provider_key_id?: int|null, error_code?: string|null, retryable: bool}
     */
    public function processTask(AiFeatureTask $task): array
    {
        try {
            if ($skip = $this->skipIfSubjectMissing($task)) {
                return $skip;
            }

            $providerKey = $this->resolveProviderForTask($task);

            if (! $providerKey) {
                return [
                    'status' => false,
                    'message' => __('AI provider is not configured for this content type.'),
                    'error_code' => 'provider_missing',
                    'retryable' => false,
                ];
            }

            $provider = $providerKey->provider->getProviderInstance();
            $config = $this->prepareProviderConfig($provider->getDefaultConfig(), $providerKey->config ?? []);
            $payload = $task->payload ?? [];

            $processor = $this->resolveContentProcessor($task->content_type);
            if (! $processor) {
                return [
                    'status' => false,
                    'message' => __('Unsupported content type.'),
                    'error_code' => 'unsupported_type',
                    'retryable' => false,
                ];
            }

            $response = $processor->process($provider, $config, $payload, $task);

            $response['provider_key_id'] = $providerKey->id;

            if (($response['status'] ?? false) === true) {
                $this->handleAiProviderKeySuccess($providerKey);
            } elseif (($response['error_code'] ?? null) === 'provider_response') {
                $threshold = (int) config('shaun_ai_features.provider.failure_threshold', 3);
                $this->handleAiProviderKeyFailure(
                    $providerKey,
                    (string) ($response['message'] ?? ''),
                    $threshold,
                    [
                        'task_id' => $task->id,
                        'content_type' => $task->content_type,
                    ]
                );
            }

            return $response;
        } catch (Throwable $e) {
            Log::error('AiFeatureTask processing error', [
                'task_id' => $task->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($providerKey)) {
                $threshold = (int) config('shaun_ai_features.provider.failure_threshold', 3);
                $this->handleAiProviderKeyFailure(
                    $providerKey,
                    $e->getMessage(),
                    $threshold,
                    [
                        'task_id' => $task->id,
                        'content_type' => $task->content_type,
                        'exception' => get_class($e),
                    ]
                );
            }

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'error_code' => 'unexpected_exception',
                'retryable' => true,
            ];
        }
    }

    /**
     * Resolve provider key id for task.
     */
    protected function resolveProviderForTask(AiFeatureTask $task): ?AiProviderKey
    {
        $providerKey = $this->resolveProviderKey($task->provider_key_id);
        if ($providerKey) {
            return $providerKey;
        }

        $fallbackId = $this->getProviderKeyIdForContentType($task->content_type);
        if ($fallbackId) {
            $fallbackKey = $this->resolveProviderKey($fallbackId);
            if ($fallbackKey) {
                if ($task->provider_key_id !== $fallbackId) {
                    $task->provider_key_id = $fallbackId;
                    $task->save();
                }

                return $fallbackKey;
            }
        }

        return null;
    }

    protected function getProviderKeyIdForContentType(string $contentType): ?int
    {
        $settingKey = $this->contentTypeSettingMap[$contentType] ?? null;

        if (! $settingKey) {
            return null;
        }

        $value = (int) setting($settingKey, 0);

        return $value > 0 ? $value : null;
    }

    protected function resolveContentProcessor(string $contentType): ?ContentTypeProcessorInterface
    {
        foreach ($this->contentProcessors as $processor) {
            if ($processor->supports($contentType)) {
                return $processor;
            }
        }

        return null;
    }

    /**
     * Merge default + stored config and append moderation system prompt instructions.
     *
     * @param array<string, mixed> $default
     * @param array<string, mixed> $stored
     * @return array<string, mixed>
     */
    protected function prepareProviderConfig(array $default, array $stored = []): array
    {
        $merged = array_merge($default, $stored);

        $moderationPrompt = trim((string) config('shaun_ai_features.moderation.system_prompt', ''));
        if ($moderationPrompt !== '') {
            $existing = trim((string) ($merged['system_prompt'] ?? ''));
            $merged['system_prompt'] = $existing !== ''
                ? $existing."\n\n".$moderationPrompt
                : $moderationPrompt;
        }

        return $merged;
    }

    /**
     * Resolve the configured provider key by id.
     */
    public function resolveProviderKey(?int $providerKeyId): ?AiProviderKey
    {
        if (! $providerKeyId) {
            return null;
        }

        /** @var AiProviderKey|null $key */
        $key = AiProviderKey::with('provider')->find($providerKeyId);

        if (! $key || ! $key->isUsable()) {
            return null;
        }

        return $key;
    }

    protected function skipIfSubjectMissing(AiFeatureTask $task): ?array
    {
        $subject = $task->getSubject();
        if ($subject) {
            return null;
        }

        return [
            'status' => true,
            'message' => __('Subject no longer exists.'),
            'data' => [
                'flagged' => false,
                'summary' => __('Content already removed before moderation ran.'),
                'reasons' => ['content_removed'],
                'details' => ['skipped' => true],
            ],
            'provider_key_id' => $task->provider_key_id,
            'retryable' => false,
        ];
    }

}
