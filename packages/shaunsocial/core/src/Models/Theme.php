<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class Theme extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'name',
        'is_active',
        'settings',
        'settings_dark'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function isDefault()
    {
        return $this->id == config('shaun_core.theme.default_id');
    }

    public function getSettings()
    {
        $result = json_decode($this->settings, true);

        return $this->combineSettings($result, getThemeSettingDefault());
    }

    public function getSettingsDark()
    {
        $result = json_decode($this->settings_dark, true);

        return $this->combineSettings($result, getThemeSettingDarkDefault());
    }

    public function combineSettings($settings, $data)
    {
        if ($settings) {
            foreach ($data as $group => &$values) {
                if (isset($settings[$group])) {
                    $values = array_intersect_key($settings[$group], $values) + $values;                    
                }
            }
        }
        return $data;
    }

    public function getPathCss()
    {
        return base_path('public').'/themes/custom_'.$this->id.'.css';
    }

    public function getAssetCss()
    {
        return asset('themes/custom_'.$this->id.'.css');
    }

    public static function getActive()
    {
        return Cache::rememberForever('theme_active', function () {
            $theme = self::where('is_active', true)->where('id', '!=', config('shaun_core.theme.default_id'))->first();
            return is_null($theme) ? false : $theme;
        });
    }

    public static function clearCache()
    {
        Cache::forget('theme_active');
    }

    public static function booted()
    {
        parent::booted();

        static::deleted(function ($theme) {
            if ($theme->is_active) {
                self::where('id', config('shaun_core.theme.default_id'))->update(['is_active' => 1]);
            }

            $path = $theme->getPathCss();
            if (file_exists($path)) {
                unlink($path);
            }

            self::clearCache();
        });
    }
}
