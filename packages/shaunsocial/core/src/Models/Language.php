<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class Language extends Model
{
    use HasTranslations;

    protected $translatable = [
        'name'
    ];
    
    protected $fillable = [
        'key',
        'name',
        'is_default',
        'is_rtl',
        'is_active'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_rtl' => 'boolean',
        'is_active' => 'boolean',
    ];


    public function canDelete()
    {
        return  $this->id != config('shaun_core.language.en_id') && ! $this->is_default;
    }

    public static function getAll($all = true)
    {
        $results = Cache::rememberForever('languages', function () {
            return self::all();
        });

        if ($all) {
            return $results;
        }

        return $results->filter(function ($value, $key) {
            return $value->is_active;
        });
    }

    public static function getCountActive()
    {
        return self::where('is_active', true)->count();
    }

    public static function getBykey($key)
    {
        $languages = self::getAll();
        return $languages->first(function ($language, $index) use ($key) {
            return $language->key == $key;
        });
    }

    protected static function booted()
    {
        static::deleting(function ($language) {
            Translation::where('locale', $language->key)->delete();

            if ($language->is_default) {
                self::where('id', config('shaun_core.language.en_id'))->update(['is_default' => 1]);
            }
            if ($language->is_active) {
                if (self::getCountActive() == 1) {
                    self::where('id', config('shaun_core.language.en_id'))->update(['is_active' => 1]);
                }                
            }
            Cache::forget('languages');
        });

        static::saved(function ($language) {
            Cache::forget('languages');
        });
    }
}
