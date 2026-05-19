<?php


namespace Packages\ShaunSocial\Vibb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasCacheSearch;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;

class VibbSong extends Model
{
    use HasCacheQueryFields, HasStorageFiles, HasCacheSearch;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $storageFields = [
        'file_id',
    ];
    
    protected $fillable = [
        'file_id',
        'name',
        'is_active',
        'use_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $cacheSearchFields = [
        'name' => [
            'is_active'
        ],
    ];

    public function getUrl()
    {
        return $this->getFile('file_id')->getUrl();
    }

    public static function getDefaultAll()
    {
        return Cache::rememberForever('vibb_songs_default', function () {
            return self::where('is_active', true)->orderBy('use_count', 'DESC')->orderBy('name')->limit(setting('feature.item_per_page'))->get();
        });
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($background) {
            Cache::forget('vibb_songs_default');
        });

        static::saved(function ($background) {
            Cache::forget('vibb_songs_default');
        });
    }
}
