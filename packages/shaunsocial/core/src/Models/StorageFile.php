<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Packages\ShaunSocial\Core\Http\Resources\Utility\StorageFileResource;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;

class StorageFile extends Model
{
    use HasCacheQueryFields, IsSubject, Prunable, HasUser;

    protected $cacheQueryFields = [
        'id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'service_key',
        'user_id',
        'parent_file_id',
        'parent_id',
        'type',
        'parent_type',
        'storage_path',
        'extension',
        'name',
        'size',
        'order',
        'has_child',
        'params',
    ];

    protected $casts = [
        'has_child' => 'boolean',
    ];

    protected $childs = null;

    public static function getResourceClass()
    {
        return StorageFileResource::class;
    }

    protected static function booted()
    {
        parent::booted();

        static::deleted(function ($file) {
            Storage::disk($file->service_key)->delete($file->storage_path);
            if ($file->getChilds()) {
                foreach ($file->getChilds() as $child) {
                    $child->delete();
                }
            }
            $file->clearCache();
        });

        static::updated(function ($file) {
            $file->clearCache();
        });
    }

    public function clearCache()
    {
        Cache::forget('storage_file_find_by_parent_type_'.$this->parent_type.'_'.$this->parent_id);

        if ($this->parent_file_id) {
            Cache::forget('storage_file_get_child_files_'.$this->parent_file_id);
        } else {
            Cache::forget('storage_file_get_child_files_'.$this->id);
        }
    }

    public function getUrl()
    {
        return Storage::disk($this->service_key)->url($this->storage_path);
    }

    public static function getByParentType($type, $id)
    {
        return Cache::remember('storage_file_find_by_parent_type_'.$type.'_'.$id, config('shaun_core.cache.time.model_query'), function () use ($type, $id) {
            return self::where('parent_type', $type)->where('parent_id', $id)->get();
        });
    }

    public function getChilds()
    {
        if (! $this->childs) {
            if ($this->has_child) {
                $id = $this->id;

                $this->childs = Cache::remember('storage_file_get_child_files_'.$this->id, config('shaun_core.cache.time.model_query'), function () use ($id) {
                    return self::where('parent_file_id', $id)->get();
                });
            } else {
                $this->childs = [];
            }
        }

        return $this->childs;
    }

    public function setChilds($childs)
    {
        $this->childs = $childs;
    }

    public function getParams()
    {
        if ($this->params) {
            return json_decode($this->params, true);
        }
        
        return [];
    }

    public function getChildUrl($type)
    {
        $childs = $this->getChilds();

        $child = $childs->first(function ($value, $key) use ($type) {
            return $value->type == $type;
        });

        if (! $child) {
            $child = $childs->first();
        }

        return $child->getUrl();
    }

    public function prunable()
    {
        return self::where('parent_file_id','<>',0)->where('parent_id',0)->where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }

    public function getOgImage()
    {
        return $this->getUrl();
    }

    public function canStore($userId, $type)
    {
        return (!$this->parent_id && $this->parent_type == $type && $this->isOwner($userId));
    }
}
