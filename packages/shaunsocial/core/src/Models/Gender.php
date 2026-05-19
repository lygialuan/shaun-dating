<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;

class Gender extends Model
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
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getAll()
    {
        return Cache::rememberForever('genders', function () {
            return self::where('is_active',true)->orderBy('order')->orderBy('id','DESC')->get();
        });
    }

    public static function getAllKeyValue()
    {
        return self::getAll()->mapWithKeys(function($gender, $key) {									
            return [$gender->id => $gender->getTranslatedAttributeValue('name')];
        })->toArray();
    }

    public function getColorReport()
    {
        $colors = config('shaun_core.gender.report_colors');
        $position = $this->id % count($colors) + 1;
    
        return $colors[$position];
    }

    protected static function booted()
    {
        static::deleting(function ($gender) {
            Cache::forget('genders');
        });

        static::saved(function ($gender) {
            Cache::forget('genders');
        });
    }
}
