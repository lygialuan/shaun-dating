<?php


namespace Packages\ShaunSocial\Core\Models;

use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Packages\ShaunSocial\Core\Traits\HasPermissions;

class LayoutContent extends Model
{
    use HasCacheQueryFields;
    use HasTranslations;
    use HasPermissions;
    
    protected $translatable = [
        'title',
        'content'
    ];

    protected $cacheQueryFields = [
        'page_id'
    ];

    protected $fillable = [
        'page_id',
        'view_type',
        'position',
        'title',
        'type',
        'content',
        'class',
        'enable_title',
        'component',
        'package',
        'params',
        'order'
    ];

    protected $casts = [
        'enable_title' => 'boolean',
    ];

    public static function getViewTypes()
    {
        return [
            'desktop' => __('Desktop and Tablet'),
            'mobile' => __('Mobile')
        ];
    }

    public static function getContents($id, $viewType, $roleId = null)
    {
        $contents = Cache::rememberForever('layout_page_contents_' . $id. '_' .$viewType, function () use ($id, $viewType) {
            $builder = LayoutContent::where('page_id', $id);
            if ($viewType) {
                $builder->where('view_type', $viewType);
            }
            return $builder->get()->sortBy('order');

        });

        if ($roleId) {
            $contents = $contents->filter(function ($value, $key) use ($roleId) {
                return $value->hasPermission($roleId);
            });
        }

        $postions = collect([
            'top' => [],
            'center' => [],
            'right' => [],
            'bottom' => []
        ]);
        return $postions->merge($contents->groupBy('position'));
    }

    public function getParams()
    {
        return $this->params ? json_decode($this->params, true) : [];
    }

    public function clearCacheTranslate()
    {
        $this->clearCacheQueryFields();
        $types = self::getViewTypes();
        foreach ($types as $key =>$text) {
            Cache::forget('layout_page_contents_' . $this->page_id. '_' .$key);
        }
    }
}
