<?php

namespace Packages\ShaunSocial\AiProvider\Providers;

interface AiProviderInterface
{
    /**
     * Unique key for the provider (used for view resolution).
     */
    public function getKey(): string;

    /**
     * Human friendly provider name.
     */
    public function getName(): string;

    /**
     * Default configuration for newly created keys.
     *
     * @return array<string, mixed>
     */
    public function getDefaultConfig(): array;

    /**
     * Required configuration fields for validation.
     *
     * @return array<int, string>
     */
    public function getRequiredFields(): array;

    /**
     * Validate configuration input.
     *
     * @param array<string, mixed> $config
     * @return array{status: bool, message: string}
     */
    public function checkConfig(array $config): array;

    /**
     * Send a message using the given configuration and optional context.
     *
     * @param string $message
     * @param array<string, mixed> $config
     * @param array<string, mixed> $context
     * @return array{status: bool, message: string, data?: array}
     */
    public function sendMessage(string $message, array $config, array $context = []): array;

    /**
     * Send request that contains only image content.
     *
     * @param string $imageEncoded
     * @param array<string, mixed> $config
     * @param array<string, mixed> $context
     * @return array{status: bool, message: string, data?: array}
     */
    public function sendOnlyImage(string $imageEncoded, array $config, array $context = []): array;

    /**
     * Send request containing both text and image content.
     *
     * @param string $message
     * @param string $imageEncoded
     * @param array<string, mixed> $config
     * @param array<string, mixed> $context
     * @return array{status: bool, message: string, data?: array}
     */
    public function sendTextAndImage(string $message, string $imageEncoded, array $config, array $context = []): array;

    /**
     * Send request that contains only video content.
     *
     * @param string $videoEncoded
     * @param array<string, mixed> $config
     * @param array<string, mixed> $context
     * @return array{status: bool, message: string, data?: array}
     */
    public function sendOnlyVideo(string $videoEncoded, array $config, array $context = []): array;

}
