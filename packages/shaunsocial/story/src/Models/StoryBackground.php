<?php


namespace Packages\ShaunSocial\Story\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;

class StoryBackground extends Model
{
    use HasCacheQueryFields, HasStorageFiles, HasDeleted;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $storageFields = [
        'photo_id',
    ];
    
    protected $fillable = [
        'photo_id',
        'alias',
        'is_active',
        'is_core',
        'order'
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function getPhotoUrl()
    {
        if ($this->is_core) {
            return asset('images/story/'.$this->id.'.jpg');
        }
        if ($this->photo_id) {
            return $this->getFile('photo_id')->getUrl();
        }
    }

    public function canDelete()
    {
        return ! $this->is_core;
    }

    public static function getAll()
    {
        return Cache::rememberForever('story_backgrounds', function () {
            return self::where('is_active', true)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($background) {
            Cache::forget('story_backgrounds');
        });

        static::saved(function ($background) {
            Cache::forget('story_backgrounds');
        });
    }
}
