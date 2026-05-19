<?php

namespace Packages\ShaunSocial\AiFeatures\Services\Helpers;

use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ContentTypeProcessorInterface;

class ProcessorTextModeration extends AbstractContentTypeProcessor implements ContentTypeProcessorInterface
{
    public function supports(string $contentType): bool
    {
        return $contentType === 'text';
    }

    public function process($provider, array $config, array $payload, AiFeatureTask $task): array
    {
        $text = (string) ($payload['text'] ?? '');

        if ($text === '') {
            return [
                'status' => false,
                'message' => __('Task payload missing text content.'),
                'error_code' => 'missing_payload',
                'retryable' => false,
            ];
        }

        $context = is_array($payload['context'] ?? null) ? $payload['context'] : [];

        $response = $provider->sendMessage($text, $config, $context);

        return $this->buildTaskResultFromProvider($response);
    }
}
