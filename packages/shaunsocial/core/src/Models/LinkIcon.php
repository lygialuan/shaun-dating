<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\IsSubject;

class LinkIcon extends Model
{
    use HasStorageFiles, IsSubject;

    protected $storageFields = [
        'icon_file_id',
    ];

    protected $fillable = [
        'domain',
        'default',
        'icon_file_id',
        'is_active'
    ];

    public static function getAll()
    {
        return Cache::rememberForever('link_icons', function () {
            return self::where('is_active', true)->get();
        });
    }

    public function getIcon()
    {
        if ($this->icon_file_id) {
            return $this->getFile('icon_file_id')->getUrl();
        }

        if ($this->default) {
            return asset($this->default);
        }

        return '';
    }

    protected static function booted()
    {
        static::deleting(function ($icon) {
            Cache::forget('link_icons');
        });

        static::saved(function ($icon) {
            Cache::forget('link_icons');
        });
    }
}
