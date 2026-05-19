<?php

namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Enum\PhotoVerifyItemStatus;

class PhotoVerifyItem extends Model
{
    use HasCacheQueryFields, HasSubject, Prunable;
    
    protected $cacheQueryFields = [
        'id',
    ];
    
    protected $storageFields = [
        'file_id'
    ];

    protected $fillable = [
        'user_id',
        'is_thumbnail',
        'order',
        'status'
    ];

    protected $casts = [
        'status' => PhotoVerifyItemStatus::class,
        'is_thumbnail' => 'boolean'
    ];

    public function getFile()
    {
        if ($this->subject_id) {
            $file = StorageFile::findByField('id', $this->subject_id);
            if ($file) {
                return $file;
            }
        }

        return null;
    }

    public static function getPhotosVerify($userId, $status = null)
    {
        $cacheKey = "photos_verify_{$userId}_" . ($status ?? 'all');

        return Cache::rememberForever($cacheKey, function () use ($userId, $status) {
            return self::query()
                ->where('user_id', $userId)
                ->when(!is_null($status), function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->where('subject_id', '<>', 0)
                ->orderBy('is_thumbnail', 'desc')
                ->orderBy('order', 'asc')
                ->limit(setting('feature.limit_photos_verify'))
                ->get();
        });
    }

    public static function getAvatar($userId)
    {
        return Cache::rememberForever("get_avatar_{$userId}", function () use ($userId) {
            return self::query()->where('user_id', $userId)->where('status', PhotoVerifyItemStatus::APPROVE->value)->where('subject_id','<>', 0)->where('is_thumbnail', 1)->first();
        });
    }

    public function clearCache()
    {
        Cache::forget("photos_verify_{$this->user_id}_all");
        Cache::forget("photos_verify_{$this->user_id}_".PhotoVerifyItemStatus::APPROVE->value);
        Cache::forget("photos_verify_{$this->user_id}_".PhotoVerifyItemStatus::PENDING->value);
        Cache::forget("get_avatar_{$this->user_id}");
    }

    protected static function booted() {
        parent::booted();
        
        static::updating(function ($photoVerifyItem) {
            if ($photoVerifyItem->is_thumbnail) {
                static::where('user_id', $photoVerifyItem->user_id)->where('id', '!=', $photoVerifyItem->id)->update(['is_thumbnail' => 0]);
            }
            $photoVerifyItem->clearCache();
        });

        static::creating(function ($photoVerifyItem) {
            if ($photoVerifyItem->is_thumbnail) {
                static::where('user_id', $photoVerifyItem->user_id)->where('id', '!=', $photoVerifyItem->id)->update(['is_thumbnail' => 0]);
            }
            $photoVerifyItem->clearCache();
        });
    }

    public function prunable()
    {
        return static::where('subject_id', 0)
            ->where(
                'created_at', 
                '<',
                now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page')
            );
    }
}
