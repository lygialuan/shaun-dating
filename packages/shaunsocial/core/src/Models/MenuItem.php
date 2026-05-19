<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasPermissions;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations, HasPermissions, HasStorageFiles, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'name',
    ];

    protected $storageFields = [
        'icon_file_id'
    ];

    protected $fillable = [
        'name',
        'menu_id',
        'parent_id',
        'is_active',
        'is_new_tab',
        'icon_file_id',
        'url',
        'type',
        'is_header',
        'is_core',
        'alias',
        'order',
        'icon_default'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_new_tab' => 'boolean',
        'is_header' => 'boolean',
        'is_core' => 'boolean',
    ];

    public function getIcon()
    {
        if ($this->icon_file_id) {
            $file = StorageFile::findByField('id', $this->icon_file_id);
            if ($file) {
                return $file->getUrl();
            }
        } elseif ($this->icon_default) {
            return asset($this->icon_default);
        }

        return null;
    }

    public function childs()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function canDelete()
    {
        return ! $this->is_core;
    }

    public static function getKeyCache($menuId)
    {
        return 'menu_item_' . $menuId;
    }

    public static function getItem($menuId)
    {
        return Cache::rememberForever(self::getKeyCache($menuId), function () use ($menuId) {
            return self::where('menu_id', $menuId)->where('parent_id', 0)->orderBy('order')->with('childs')->get();
        });
    }

    public function clearCache()
    {
        Cache::forget(self::getKeyCache($this->menu_id));
    }

    public function clearCacheTranslate()
    {
        $this->clearCache();
    }

    protected static function booted()
    {
        parent::booted();

        static::saving(function ($menuItem) {
            $menuItem->clearCache();
        });

        static::deleting(function ($menuItem) {
            $menuItem->clearCache();
            if (count($menuItem->childs)) {
                foreach ($menuItem->childs as $child) {
                    if ($child->canDelete()) {
                        $child->delete();
                    } else {
                        $child->update([
                            'parent_id' => 0
                        ]);
                    }
                }
            }
        });

        static::creating(function ($menuItem) {            
            if (! $menuItem->order) {
                $last = MenuItem::latest()->first();
                if ($last) {
                    $menuItem->order = $last->id;
                }
            }
        });
    }
}
