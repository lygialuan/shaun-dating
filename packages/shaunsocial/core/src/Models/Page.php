<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasPermissions;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class Page extends Model
{
    use HasTranslations, HasPermissions, HasCacheQueryFields;

    protected $translatable = [
        'content',
    ];

    protected $fillable = [
        'slug',
        'content',
    ];

    protected $cacheQueryFields = [
        'id',
        'slug'
    ];

    public function getLayout()
    {
        return LayoutPage::findByField('page_id', $this->id);
    }

    public function getHref()
    {
        if ($this->id) {
            return route('web.page.detail',[
                'slug' => $this->slug
            ]);
        }
        
        return  '';
    }

    protected static function booted()
    {
        parent::booted();

        static::deleted(function ($page) {
            LayoutPage::where('page_id', $page->id)->get()->each(function($layoutPage) {
                $layoutPage->delete();
            });
        });
    }
}
