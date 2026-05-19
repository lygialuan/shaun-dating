<?php

namespace Packages\ShaunSocial\AiFeatures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\ShaunSocial\Core\Models\StorageFile;

class AiFeatureTaskItem extends Model
{
    protected $fillable = [
        'ai_feature_task_id',
        'storage_file_id',
        'original_file_id',
        'item_type',
        'item_subject_type',
        'item_subject_id',
        'item_order',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiFeatureTask::class, 'ai_feature_task_id');
    }

    public function storageFile(): BelongsTo
    {
        return $this->belongsTo(StorageFile::class, 'storage_file_id');
    }

    protected static function booted()
    {
        parent::booted();

        static::deleted(function (AiFeatureTaskItem $item) {
            if ($item->storageFile) {
                $item->storageFile->delete();
            }
        });
    }
}
