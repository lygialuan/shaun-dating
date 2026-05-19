<?php

namespace Packages\ShaunSocial\AiFeatures\Services\Helpers;

use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;

interface ContentTypeProcessorInterface
{
    public function supports(string $contentType): bool;

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $payload
     * @return array{status: bool, message: string, data?: array, error_code?: string|null, retryable: bool}
     */
    public function process($provider, array $config, array $payload, AiFeatureTask $task): array;
}
