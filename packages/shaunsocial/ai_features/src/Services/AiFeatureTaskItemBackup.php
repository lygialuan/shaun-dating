<?php

namespace Packages\ShaunSocial\AiFeatures\Services;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTaskItem;
use Packages\ShaunSocial\Core\Models\CommentItem;
use Packages\ShaunSocial\Core\Models\CommentReplyItem;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Models\StorageFile;

class AiFeatureTaskItemBackup
{
    public function cloneSubjectItems(AiFeatureTask $task): void
    {
        if (AiFeatureTaskItem::where('ai_feature_task_id', $task->id)->exists()) {
            return;
        }

        $subject = $task->getSubject();
        if (! $subject || ! method_exists($subject, 'getItems')) {
            return;
        }

        $items = $subject->getItems();
        if (! $items instanceof Collection && ! is_iterable($items)) {
            return;
        }

        $order = 0;

        foreach ($items as $item) {
            $subjectModel = method_exists($item, 'getSubject') ? $item->getSubject() : null;
            $file = $subjectModel instanceof StorageFile ? $subjectModel : $this->resolveStorageFileFromItem($item);
            $snapshotFile = $file ? $this->duplicateStorageFile($file, $task) : null;
            $metadata = $this->buildMetadataPayload($subjectModel, $file, $snapshotFile);

            if (! $snapshotFile && empty($metadata)) {
                continue;
            }

            AiFeatureTaskItem::create([
                'ai_feature_task_id' => $task->id,
                'storage_file_id' => $snapshotFile?->id,
                'original_file_id' => $file?->id,
                'item_type' => $item->subject_type ?? $file?->type ?? data_get($subjectModel, 'type'),
                'item_subject_type' => $this->resolveItemSubjectType($item, $subjectModel),
                'item_subject_id' => $this->resolveItemSubjectId($item, $subjectModel),
                'item_order' => $order,
                'metadata' => empty($metadata) ? null : $metadata,
            ]);

            $order++;
        }
    }

    protected function resolveStorageFileFromItem(mixed $item): ?StorageFile
    {
        if ($item instanceof PostItem || $item instanceof CommentItem || $item instanceof CommentReplyItem) {
            $subject = $item->getSubject();
            if ($subject instanceof StorageFile) {
                return $subject;
            }
        }

        return null;
    }

    protected function duplicateStorageFile(StorageFile $original, AiFeatureTask $task): ?StorageFile
    {
        $diskName = $original->service_key ?: config('filesystems.default');
        $disk = Storage::disk($diskName);

        if (! $disk->exists($original->storage_path)) {
            Log::warning('AI task item clone skipped: source file missing.', [
                'task_id' => $task->id,
                'file_id' => $original->id,
            ]);

            return null;
        }

        $extension = $original->extension ?: pathinfo($original->storage_path, PATHINFO_EXTENSION);
        $targetPath = sprintf(
            'ai_task_items/task-%d/%s%s',
            $task->id,
            Str::uuid()->toString(),
            $extension ? '.'.$extension : ''
        );

        $directory = dirname($targetPath);
        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $disk->put($targetPath, $disk->get($original->storage_path));

        return StorageFile::create([
            'service_key' => $original->service_key,
            'user_id' => $original->user_id,
            'parent_file_id' => 0,
            'parent_id' => $task->id,
            'type' => $original->type,
            'parent_type' => 'ai_feature_task',
            'storage_path' => $targetPath,
            'extension' => $extension,
            'name' => $original->name ?? 'ai_task_item_'.$original->id,
            'size' => $disk->size($targetPath) ?: $original->size,
            'order' => $original->order ?? 0,
            'has_child' => false,
            'params' => json_encode([
                'original_file_id' => $original->id,
                'original_storage_path' => $original->storage_path,
            ]),
        ]);
    }

    protected function resolveItemSubjectType(mixed $item, mixed $subject): ?string
    {
        if (isset($item->subject_type)) {
            return (string) $item->subject_type;
        }

        if (is_object($subject)) {
            if (method_exists($subject, 'getSubjectType')) {
                return $subject->getSubjectType();
            }

            return get_class($subject);
        }

        return null;
    }

    protected function resolveItemSubjectId(mixed $item, mixed $subject): ?int
    {
        if (isset($item->subject_id)) {
            return (int) $item->subject_id;
        }

        if ($subject instanceof Model) {
            return (int) $subject->getKey();
        }

        if (is_object($subject) && isset($subject->id)) {
            return (int) $subject->id;
        }

        return null;
    }

    protected function buildMetadataPayload(mixed $subject, ?StorageFile $originalFile, ?StorageFile $snapshotFile): array
    {
        $metadata = [
            'subject_class' => $subject ? get_class($subject) : null,
            'subject_snapshot' => $this->extractSubjectSnapshot($subject),
        ];

        if ($originalFile) {
            $metadata['original_storage_path'] = $originalFile->storage_path;
            $metadata['original_service_key'] = $originalFile->service_key;
            $metadata['original_extension'] = $originalFile->extension;
        }

        if ($snapshotFile) {
            $metadata['snapshot_storage_path'] = $snapshotFile->storage_path;
            $metadata['snapshot_service_key'] = $snapshotFile->service_key;
        }

        return array_filter($metadata, static function ($value) {
            return $value !== null && $value !== [];
        });
    }

    protected function extractSubjectSnapshot(mixed $subject): mixed
    {
        if (! $subject || $subject instanceof StorageFile) {
            return null;
        }

        if ($subject instanceof Arrayable) {
            return $subject->toArray();
        }

        if ($subject instanceof Model) {
            return $subject->attributesToArray();
        }

        if (is_array($subject)) {
            return $subject;
        }

        if (is_scalar($subject)) {
            return $subject;
        }

        if (is_object($subject) && method_exists($subject, '__toString')) {
            return (string) $subject;
        }

        return null;
    }
}
