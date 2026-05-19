<?php

namespace Packages\ShaunSocial\Gift\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;

class Gift extends Model
{
    use HasCacheQueryFields, HasDeleted, HasTranslations, HasStorageFiles;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'name',
    ];

    protected $storageFields = [
        'icon'
    ];

    protected $fillable = [
        'name',
        'icon',
        'icon_default',
        'price',
        'is_active',
        'is_delete',
        'order'
    ];

    protected $casts = [
        'price' => 'integer',
        'is_active' => 'boolean',
        'is_delete' => 'boolean'
    ];

    
    public static function getAll()
    {
        return Cache::rememberForever('gifts', function () {
            return self::where('is_active', true)->orderBy('order')->limit(50)->get();
        });
    }

    public function getPrice()
    {
        return formatNumber($this->price, 0);
    }

    public function getIconUrl()
    {
        $iconFile = $this->getFile('icon');
        if($iconFile){
            return $iconFile->getUrl();
        }else if($this->icon_default){
            return asset($this->icon_default);
        }
        return null;
    }

    public function clearCache()
    {
        Cache::forget('gifts');
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($gift) {
            $gift->clearCache();
        });

        static::saved(function ($gift) {
            $gift->clearCache();
        });
        
        static::creating(function ($gift) {
            $last = self::latest()->first();
            if ($last) {
                $gift->order = $last->id;
            }
        });
    }
}