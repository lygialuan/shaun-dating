<?php

namespace Packages\ShaunSocial\AiFeatures\Services\Helpers;

use Illuminate\Support\Facades\Storage;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Services\Helpers\ContentTypeProcessorInterface;
use Packages\ShaunSocial\Core\Models\StorageFile;

class ProcessorImageModeration extends AbstractContentTypeProcessor implements ContentTypeProcessorInterface
{
    public function supports(string $contentType): bool
    {
        return $contentType === 'image';
    }

    public function process($provider, array $config, array $payload, AiFeatureTask $task): array
    {
        $image = (string) ($payload['image_base64'] ?? '');
        $mime = (string) ($payload['image_mime_type'] ?? '');
        $text = (string) ($payload['text'] ?? '');

        if ($image === '') {
            [$image, $mime] = $this->loadImageFromTask($task);
        }

        if ($image === '') {
            return [
                'status' => false,
                'message' => __('Task payload missing image content.'),
                'error_code' => 'missing_payload',
                'retryable' => false,
            ];
        }

        $context = is_array($payload['context'] ?? null) ? $payload['context'] : [];
        if ($mime !== '') {
            $context['mime_type'] = $mime;
        }

        $response = $provider->sendOnlyImage($image, $config, $context);

        return $this->buildTaskResultFromProvider($response);
    }

    /**
     * @return array{string,string}
     */
    protected function loadImageFromTask(AiFeatureTask $task): array
    {
        if (! $task->content_ref_type || ! $task->content_ref_id) {
            return ['', ''];
        }

        $file = \findByTypeId($task->content_ref_type, $task->content_ref_id);
        if (! $file instanceof StorageFile) {
            return ['', ''];
        }

        $diskName = $file->service_key ?: config('filesystems.default');
        $disk = Storage::disk($diskName);

        if (! $disk->exists($file->storage_path)) {
            return ['', ''];
        }

        $contents = $disk->get($file->storage_path);
        if (! is_string($contents) || $contents === '') {
            return ['', ''];
        }

        $mime = Storage::mimeType($file->storage_path) ?? $this->guessImageMime($file);

        return [base64_encode($contents), $mime];
    }

    protected function guessImageMime(StorageFile $file): string
    {
        $extension = strtolower((string) $file->extension);

        return match ($extension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            default => 'image/jpeg',
        };
    }
}
