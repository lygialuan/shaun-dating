<?php

namespace Packages\ShaunSocial\UserVerify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;

class UserVerifyFile extends Model
{
    use Prunable, HasCacheQueryFields, HasStorageFiles, Prunable;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'user_id',
        'file_id',
        'is_accept'
    ];

    protected $casts = [
        'is_accept' => 'boolean',
    ];

    protected $storageFields = [
        'file_id',
    ];

    public function canStore($viewerId)
    {
        return $this->user_id == $viewerId && ! $this->is_accept;
    }

    public static function getCacheUserKey($userId)
    {
        return 'user_verify_files_'.$userId;
    }

    static function getFilesByUserId($userId)
    {
        return Cache::remember(self::getCacheUserKey($userId), config('shaun_core.cache.time.model_query'), function () use ($userId) {
            return self::where('user_id', $userId)->where('is_accept', true)->get();
        });
    }

    public function canDelete($userId)
    {
        return $this->canStore($userId);
    }

    static function deleteByUser($user)
    {
        $files = $user->getVerifyFiles();
        $files->each(function($file){
            $file->delete();
        });
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheUserKey($this->user_id));
    }

    public function prunable()
    {
        return static::where('is_accept', 0)->where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($file) {
            $file->clearCache();
        });

        static::deleted(function ($file) {
            $file->clearCache();
        });
    }
}
