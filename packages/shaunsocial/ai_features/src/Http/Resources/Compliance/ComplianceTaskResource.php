<?php

namespace Packages\ShaunSocial\AiFeatures\Http\Resources\Compliance;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;

class ComplianceTaskResource extends JsonResource
{
    public function toArray($request): array
    {
        $summary = $this->getResultSummary() ?: [];
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');

        return [
            'id' => $this->id,
            'status' => $this->status,
            'content_type' => $this->content_type,
            'auto_action' => $this->auto_action,
            'violator' => $this->resolveViolatorUser(),
            'timeline' => [
                'queued_at' => optional($this->created_at)->toIso8601String(),
                'processed_at' => optional($this->processed_at)->toIso8601String(),
                'reported_at' => $this->reported_at->setTimezone($timezone)->diffForHumans(),
            ],
            'summary' => [
                'flagged' => (bool) data_get($summary, 'flagged', false),
                'message' => $this->buildRemovalMessage(),
                'reasons' => array_values(data_get($summary, 'reasons', []) ?: []),
            ],
            'content' => $this->formatSubjectContent(),
            'media' => $this->formatBackupMedia(),
        ];
    }

    protected function buildRemovalMessage(): string
    {
        return match ($this->subject_type) {
            'posts', 'post' => __('Your post was removed because it violates our community standards.'),
            'comments', 'comment' => __('Your comment was removed because it violates our community standards.'),
            'comment_replies', 'comment_reply' => __('Your comment reply was removed because it violates our community standards.'),
            default => __('Your content was removed because it violates our community standards.'),
        };
    }

    protected function formatBackupMedia(): array
    {
        $items = $this->resource->getItems();
        $collection = $items instanceof Collection ? $items : collect($items ?: []);

        if ($collection->isEmpty()) {
            return [];
        }

        $collection->load('storageFile');

        return $collection->map(function ($item) {
            $storageFile = $item->storageFile;
            if (! $storageFile) {
                return null;
            }

            $url = AiFeatureTask::resolveStorageUrl($storageFile);
            if (! $url) {
                return null;
            }

            return [
                'type' => $this->classifyMediaType($storageFile, $item),
                'url' => $url,
            ];
        })->filter()->values()->all();
    }

    protected function formatSubjectContent(): ?array
    {
        $rawContent = $this->extractContent();
        if (! $rawContent) {
            return null;
        }

        return [
            'type' => $this->getSubjectLabel(),
            'excerpt' => Str::limit(strip_tags($rawContent), 500),
        ];
    }

    protected function extractContent(): ?string
    {
        foreach (['content', 'text', 'message', 'body'] as $key) {
            $value = data_get($this->payload, $key);
            if (! empty($value)) {
                return (string) $value;
            }
        }

        $subject = $this->getSubject();
        if (! $subject) {
            return null;
        }

        foreach (['content', 'message', 'body', 'text'] as $field) {
            if (! empty($subject->{$field})) {
                return (string) $subject->{$field};
            }
        }

        if (method_exists($subject, 'getContent')) {
            return (string) $subject->getContent();
        }

        return null;
    }

    protected function classifyMediaType(StorageFile $file, $item): string
    {
        $extension = strtolower($file->extension ?? pathinfo($file->storage_path ?? '', PATHINFO_EXTENSION));
        if (! $extension && ! empty($item->item_type)) {
            $extension = strtolower((string) $item->item_type);
        }

        return match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']) => 'image',
            in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'webm']) => 'video',
            in_array($extension, ['mp3', 'wav', 'aac', 'm4a', 'flac']) => 'audio',
            default => 'file',
        };
    }

    protected function resolveViolatorUser(): ?UserResource
    {
        $userId = data_get($this->payload, 'context.user_id') ?? data_get($this->payload, 'user_id');
        if ($userId) {
            $user = User::findByField('id', $userId);
            if ($user) {
                return new UserResource($user);
            }
        }

        $subject = $this->getSubject();
        if ($subject instanceof User) {
            return new UserResource($subject);
        }

        if ($subject && method_exists($subject, 'getUser')) {
            $user = $subject->getUser();
            if ($user) {
                return new UserResource($user);
            }
        }

        return null;
    }
}
