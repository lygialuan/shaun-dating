<?php

namespace Packages\ShaunSocial\AiFeatures\Services\Helpers;

use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ContentTypeProcessorInterface;

class ProcessorVideoModeration extends AbstractContentTypeProcessor implements ContentTypeProcessorInterface
{
    public function supports(string $contentType): bool
    {
        return $contentType === 'video';
    }

    public function process($provider, array $config, array $payload, AiFeatureTask $task): array
    {
        $video = (string) ($payload['video_base64'] ?? '');

        if ($video === '') {
            return [
                'status' => false,
                'message' => __('Task payload missing video content.'),
                'error_code' => 'missing_payload',
                'retryable' => false,
            ];
        }

        $response = $provider->sendOnlyVideo($video, $config, $payload['context'] ?? []);

        return $this->buildTaskResultFromProvider($response);
    }
}
