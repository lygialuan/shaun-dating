<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;

class ReportCategory extends Model
{
    use HasTranslations, HasDeleted, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'name',
    ];

    protected $fillable = [
        'name',
        'is_active',
        'is_ai',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function canDelete()
    {
        return $this->id != config('shaun_core.report.other_id') && ! $this->is_ai;
    }

    public static function getAll($all = true)
    {
        $result = Cache::rememberForever('report_categories', function () {
            return self::where('is_active',true)->orderBy('order')->orderBy('id','DESC')->get();
        });

        if ($all) {
            return $result;
        } else {
            return $result->filter(function  ($value, $key) {
                return $value->canStore();
            });
        }
    }

    public function canStore()
    {
        return ! $this->isDeleted() && ! $this->is_ai;
    }

    public static function getCategoryAi()
    {
        return self::where('is_ai', true)->first();
    }

    protected static function booted()
    {
        static::deleting(function ($category) {
            Cache::forget('report_categories');
        });

        static::saved(function ($category) {
            Cache::forget('report_categories');
        });
    }
}
