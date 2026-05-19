<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;

class ContentWarningCategory extends Model
{
    use HasTranslations, HasDeleted, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'name'
    ];

    protected $fillable = [
        'name',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getAll()
    {
        return Cache::rememberForever('content_warning_categories', function () {
            return self::where('is_active', true)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    public function clearCacheTranslate()
    {
        $this->clearCacheQueryFields();
        $this->clearCache();
    }

    public function clearCache()
    {
        Cache::forget('content_warning_categories');
    }

    public function canDelete()
    {
        return $this->id != config('shaun_core.content_warning.other_id');
    }

    protected static function booted()
    {
        static::deleting(function ($category) {
            $category->clearCache();
        });

        static::creating(function ($category) {
            $last = ContentWarningCategory::latest()->first();
            if ($last) {
                $category->order = $last->id;
            }
        });

        static::saved(function ($category) {
            $category->clearCache();
        });
    }
}
