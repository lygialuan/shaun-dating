<?php

namespace Packages\ShaunSocial\AiProvider\Providers;

abstract class AbstractAiProvider implements AiProviderInterface
{
    /**
     * Default configuration values.
     *
     * @var array<string, mixed>
     */
    protected array $defaultConfig = [];

    /**
     * Required configuration fields.
     *
     * @var array<int, string>
     */
    protected array $requiredFields = [];

    /**
     * Unique key for the provider (used for view mapping).
     */
    abstract public function getKey(): string;

    /**
     * Providers must implement message sending.
     *
     * @param string $message
     * @param array<string, mixed> $config
     * @param array<string, mixed> $context
     * @return array{status: bool, message: string, data?: array}
     */
    abstract public function sendMessage(string $message, array $config, array $context = []): array;

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig(): array
    {
        return $this->defaultConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig(array $config): array
    {
        foreach ($this->requiredFields as $field) {
            if (empty($config[$field]) && $config[$field] !== 0 && $config[$field] !== '0') {
                return [
                    'status' => false,
                    'message' => $this->missingFieldMessage($field),
                ];
            }
        }

        return [
            'status' => true,
            'message' => __('Configuration is valid.'),
        ];
    }

    /**
     * Provide user-friendly messages for missing fields.
     */
    protected function missingFieldMessage(string $field): string
    {
        return match ($field) {
            'api_key' => __('The API key is required.'),
            'model' => __('The model field is required.'),
            'max_output_tokens', 'max_tokens_output' => __('The max output tokens value is required.'),
            'max_tokens' => __('The max tokens value is required.'),
            'temperature' => __('The temperature value is required.'),
            'system_prompt' => __('The system prompt is required.'),
            default => __('Configuration is invalid.'),
        };
    }

    /**
     * Validate message before sending.
     */
    protected function validateMessage(string $message): bool
    {
        return ! empty(trim($message)) && strlen($message) <= 4000;
    }

    /**
     * Format error response.
     *
     * @param string $message
     * @return array{status: bool, message: string, data: null}
     */
    protected function errorResponse(string $message): array
    {
        return [
            'status' => false,
            'message' => $message,
            'data' => null,
        ];
    }

    /**
     * Format success response.
     *
     * @param string $message
     * @param array<string, mixed> $data
     * @return array{status: bool, message: string, data: array}
     */
    protected function successResponse(string $message, array $data = []): array
    {
        return [
            'status' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Generic user-facing message for provider connectivity issues.
     */
    protected function providerErrorMessage(?string $detail = null): string
    {
        $message = __('Chat bot is currently unavailable. Please contact admin for more details.');

        if (config('app.debug') && $detail) {
            return $detail;
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function sendOnlyImage(string $imageEncoded, array $config, array $context = []): array
    {
        return $this->errorResponse(__('This provider does not support image-only requests.'));
    }

    /**
     * {@inheritdoc}
     */
    public function sendTextAndImage(string $message, string $imageEncoded, array $config, array $context = []): array
    {
        return $this->errorResponse(__('This provider does not support text and image requests.'));
    }

    /**
     * {@inheritdoc}
     */
    public function sendOnlyVideo(string $videoEncoded, array $config, array $context = []): array
    {
        return $this->errorResponse(__('This provider does not support video-only requests.'));
    }
}
