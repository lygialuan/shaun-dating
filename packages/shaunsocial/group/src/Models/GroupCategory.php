<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;

class GroupCategory extends Model
{
    use HasTranslations, HasDeleted, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'name',
    ];

    protected $fillable = [ // define saved data when use create($data) and update($data)
        'name',
        'parent_id',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function childs()
    {
        return $this->hasMany(GroupCategory::class, 'parent_id')->orderBy('order');
    }

    public static function getAll()
    {
        return Cache::rememberForever('group_categories', function () {
            return self::where('parent_id', 0)->orderBy('order')->with('childs')->get();
        });
    }

    public function canUse()
    {
        return $this->is_active && ! $this->isDeleted();
    }

    public function clearCacheTranslate()
    {
        $this->clearCacheQueryFields();
        $this->clearCache();
    }

    public function clearCache()
    {
        Cache::forget('group_categories');
    }

    protected static function booted()
    {
        parent::booted();

        static::saving(function ($category) {
            $category->clearCache();
        });

        static::creating(function ($category) {
            $last = GroupCategory::latest()->first();
            if ($last) {
                $category->order = $last->id;
            }
        });
    }
}
