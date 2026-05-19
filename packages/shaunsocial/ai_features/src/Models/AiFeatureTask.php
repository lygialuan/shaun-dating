<?php

namespace Packages\ShaunSocial\AiFeatures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTaskItem;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Models\StorageFile;


class AiFeatureTask extends Model
{
    use HasSubject, IsSubject, HasCacheQueryFields;
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DONE = 'done';
    public const STATUS_FAILED = 'failed';

    public const AUTO_ACTION_NONE = 'none';
    public const AUTO_ACTION_FLAG = 'flag';
    public const AUTO_ACTION_HIDE = 'hide';
    public const AUTO_ACTION_DELETE = 'delete';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_type',
        'subject_id',
        'content_type',
        'content_ref_type',
        'content_ref_id',
        'provider_key_id',
        'status',
        'payload',
        'result',
        'error_code',
        'error_message',
        'attempts',
        'max_attempts',
        'next_run_at',
        'processed_at',
        'reported_at',
        'auto_action',
    ];

    /**
     * Attribute casting definitions.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'next_run_at' => 'datetime',
        'processed_at' => 'datetime',
        'reported_at' => 'datetime',
    ];

    protected $cacheQueryFields = [
        'id',
    ];

    protected $items = null;

    /**
     * Relation: moderation task belongs to a provider key.
     */
    public function providerKey(): BelongsTo
    {
        return $this->belongsTo(AiProviderKey::class, 'provider_key_id');
    }

    public function getItems()
    {
        if ($this->items === null) {
            $this->items = AiFeatureTaskItem::where('ai_feature_task_id', $this->id)
                ->orderBy('item_order')
                ->get();
        }

        return $this->items;
    }

    public function setItems($items): void
    {
        $this->items = $items;
    }

    public function getResultSummary(): ?array
    {
        $result = $this->result ?? [];
        if (! is_array($result) || empty($result)) {
            return null;
        }

        $flagged = data_get($result, 'flagged');
        $summary = trim((string) data_get($result, 'summary', ''));
        $reasons = data_get($result, 'reasons');
        if (! is_array($reasons)) {
            $reasons = [];
        }

        $details = data_get($result, 'details');
        if (! is_array($details) || empty($details)) {
            $details = data_get($result, 'response_json');
        }

        return [
            'flagged' => $flagged,
            'summary' => $summary,
            'reasons' => array_filter(array_map('trim', $reasons)),
            'details' => is_array($details) ? $details : null,
        ];
    }

    public function getIsFlaggedAttribute(): bool
    {
        return (bool) data_get($this->result, 'flagged', false);
    }

    protected static function booted()
    {
        parent::booted();

        static::deleted(function (AiFeatureTask $task) {
            $task->getItems()->each(function (AiFeatureTaskItem $item) {
                $item->delete();
            });
        });
    }

    /**
     * Resolve a human readable label for the subject type.
     */
    public function getSubjectLabel(): string
    {
        return match ($this->subject_type) {
            'posts' => __('Post'),
            'comments' => __('Comment'),
            'comment_replies' => __('Comment Reply'),
            'users' => __('User'),
            default => (string) $this->subject_type,
        };
    }

    /**
     * Resolve a link to the subject if available.
     */
    public function getHref(): ?string
    {
        $subject = $this->getSubject();
        if ($subject && method_exists($subject, 'getHref')) {
            return $subject->getHref();
        }
        return null;
    }

    public static function getReadyItems(int $limit)
    {
        return self::where('status', self::STATUS_PENDING)
            ->where(function ($query) {
                $query->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', Carbon::now());
            })
            ->whereColumn('attempts', '<', 'max_attempts')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public static function resolveStorageUrl(StorageFile $file): ?string
    {
        $disk = Storage::disk($file->service_key ?: config('filesystems.default'));
        if (! $disk->exists($file->storage_path)) {
            return null;
        }
        return $disk->url($file->storage_path) ?? null;
    }
}
