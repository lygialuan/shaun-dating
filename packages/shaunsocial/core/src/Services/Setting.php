<?php


namespace Packages\ShaunSocial\Core\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\Setting as SettingDB;
use Packages\ShaunSocial\Core\Models\StorageFile;

class Setting
{
    public $setting_cache = null;

    public function getSettingCache()
    {
        if ($this->setting_cache === null) {
            $this->setting_cache = Cache::rememberForever('settings', function () {
                try {
                    return SettingDB::orderBy('order')->get()->mapWithKeys(function ($item, $key) {
                        return [
                            $item->key => [
                                'value' => $item->value,
                                'params' => $item->getParams(),
                                'type' => $item->type,
                            ]
                        ];
                    });
                } catch (Exception $e) {
                    return [];
                }
            });
        }

        return $this->setting_cache;
    }

    public function get($key, $default = null)
    {
        $settingCache = $this->getSettingCache();
        if (! isset($settingCache[$key])) {
            return $default;
        }

        $setting = $settingCache[$key];

        if ($setting['type'] == 'image') {
            if ($setting['value']) {
                $file = StorageFile::findByField('id', $setting['value']);
                return $file->getUrl();
            } elseif (isset($setting['params']['default'])) {
                return asset($setting['params']['default']);
            }

            return $default;
        } else {
            return $setting['value'];
        }       
    }

    public function set($key, $value)
    {
        $settingCache = $this->getSettingCache();
        if ($settingCache !== null && isset($this->setting_cache[$key])) {
            $setting = $settingCache[$key];
            $setting['value'] = $value;
            $this->setting_cache[$key] = $setting;
        }
    }

    public function clear()
    {
        Cache::forget('settings');
    }
}
