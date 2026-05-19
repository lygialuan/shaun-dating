<?php

use Carbon\Carbon;
use Packages\ShaunSocial\Core\Support\Facades\Mail;
use Packages\ShaunSocial\Core\Support\Facades\Setting;
use Packages\ShaunSocial\Core\Models\ModelMap;
use Packages\ShaunSocial\Core\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\Setting as SettingModel;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\Package;
use Illuminate\Support\Facades\DB;
use libphonenumber\PhoneNumberUtil;
use Packages\ShaunSocial\Core\Models\Permission;

if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('setSetting')) {
    function setSetting($key, $value)
    {
        return Setting::set($key, $value);
    }
}

if (! function_exists('clearSettingCache')) {
    function clearSettingCache()
    {
        return Setting::clear();
    }
}

if (! function_exists('alreadyInstalled')) {
    function alreadyInstalled()
    {
        $installed = file_exists(base_path('.env')) && ! file_exists(storage_path('installed'));
        return env('ALREADY_INSTALL', $installed);
    }
}

if (! function_exists('getCacheSetting')) {
    function getCacheSetting()
    {
        $filePath = storage_path(config('shaun_core.cache.file_name_config'));
        if (file_exists($filePath)) {
            $setting = file_get_contents($filePath);

            return json_decode($setting, true);
        }

        return ['driver' => 'file'];
    }
}

if (! function_exists('setCacheConfig')) {
    function setCacheConfig($config, $isTest = false)
    {
        $cache = config('cache');

        switch ($config['driver']) {
            case 'memcached':
                $cache['stores']['memcached']['servers'][0]['host'] = $config['memcached_host'];
                $cache['stores']['memcached']['servers'][0]['port'] = $config['memcached_port'] ? $config['memcached_port'] : 11211;
                $cache['stores']['memcached']['sasl'][0] = $config['memcached_username'];
                $cache['stores']['memcached']['sasl'][1] = $config['memcached_password'];
                break;
            case 'redis':
                $database = config('database');
                $database['redis']['cache']['host'] = $config['redis_host'];
                $database['redis']['cache']['port'] = $config['redis_port'] ? $config['redis_port'] : 6379;
                $database['redis']['cache']['password'] = $config['redis_password'];
                $database['redis']['default'] = $database['redis']['cache'];
                config(['database' => $database]);
                break;
        }

        if (! $isTest) {
            $cache['default'] = $config['driver'];
        }
        config(['cache' => $cache]);
    }
}

if (! function_exists('sendMail')) {
    function sendMail($type, $to, $params)
    {
        Mail::send($type, $to, $params);
    }
}

if (! function_exists('setFileSystemsConfig')) {
    function setFileSystemsConfig($services, $isTest = false)
    {
        $file = config('filesystems');
        $default = $file['default'];
        foreach ($services as $service) {
            if ($service->is_default) {
                $default = $service->key;
            }
            $config = $service->getConfig();
            switch ($service->key) {
                case 'public':
                    if (! empty($config['url'])) {
                        $file['disks']['public']['url'] = $config['url'];
                    }
                    break;
                case 's3':
                    $file['disks']['s3']['key'] = $config['key'];
                    $file['disks']['s3']['secret'] = $config['secret'];
                    $file['disks']['s3']['region'] = $config['region'];
                    $file['disks']['s3']['bucket'] = $config['bucket'];
                    $url = $config['url'] ?? '';
                    if (! $url) {
                        $url = 'https://'.$config['bucket'].'.s3.'.$config['region'].'.amazonaws.com';
                    }

                    $file['disks']['s3']['url'] = $url;

                    break;
                case 'wasabi':
                    $file['disks']['wasabi']['key'] = $config['key'];
                    $file['disks']['wasabi']['secret'] = $config['secret'];
                    $file['disks']['wasabi']['region'] = $config['region'];
                    $file['disks']['wasabi']['bucket'] = $config['bucket'];
                    $file['disks']['wasabi']['endpoint'] = $config['url'];
                    $file['disks']['wasabi']['url'] = $config['url'].'/'.$config['bucket'];

                    break;
                case 'do':
                    $file['disks']['do']['key'] = $config['key'];
                    $file['disks']['do']['secret'] = $config['secret'];
                    $file['disks']['do']['bucket'] = $config['bucket'];
                    $file['disks']['do']['endpoint'] = $config['endpoint'];
                    if (! empty($config['url'])) {
						$file['disks']['do']['url'] = $config['url'];
					}
                    break;
                case 'r2':
                    $file['disks']['r2']['key'] = $config['key'];
                    $file['disks']['r2']['secret'] = $config['secret'];
                    $file['disks']['r2']['bucket'] = $config['bucket'];
                    $file['disks']['r2']['endpoint'] = $config['endpoint'];
                    if (! empty($config['url'])) {
                        $file['disks']['r2']['url'] = $config['url'];
                    }
                    break;
                case 'backblaze':
                    $file['disks']['backblaze']['key'] = $config['key'];
                    $file['disks']['backblaze']['secret'] = $config['secret'];
                    $file['disks']['backblaze']['bucket'] = $config['bucket'];
                    $file['disks']['backblaze']['endpoint'] = $config['endpoint'];
                    if (! empty($config['url'])) {
                        $file['disks']['backblaze']['url'] = $config['url'];
                    }
                    break;

            }
        }
        if (! $isTest) {
            $file['default'] = $default;
        }
        config(['filesystems' => $file]);
    }
}

if (! function_exists('formatNumber')) {
    function formatNumber($number, $decimals = null, $prefix = '')
    {
        if ($decimals === null) {
            $decimals = config('shaun_core.core.decimal');
        }
        return number_format($number, $decimals).($prefix ? ' '.$prefix : '');
    }
}

if (! function_exists('formatNumberNoRound')) {
    function formatNumberNoRound($number, $prefix = '')
    {
        return number_format($number).($prefix ? ' '.$prefix : '');
    }
}

if (! function_exists('getHashtagsFromContent')) {
    function getHashtagsFromContent($content)
    {
        preg_match_all(config('shaun_core.regex.hashtag'), $content, $matches);

        $results = ! empty($matches[0]) ? array_unique($matches[0]) : [];

        return collect($results)->sortByDesc(function ($result, $key) {
            return strlen($result);
        })->toArray();
    }
}

if (! function_exists('checkHashtag')) {
    function checkHashtag($hashtag)
    {
        $hashtag = '#'.$hashtag;
        $hashtags = getHashtagsFromContent($hashtag);
        if (count($hashtags) != 1) {
            return false;
        }

        return $hashtag == $hashtags[0];
    }
}

if (! function_exists('getUrlsFromContent')) {
    function getUrlsFromContent($content)
    {
        preg_match_all(config('shaun_core.regex.link'), $content, $matches);
        
        $results = ! empty($matches[0]) ? array_unique($matches[0]) : [];
        
        return collect($results)->sortByDesc(function ($result, $key) {
            return strlen($result);
        })->toArray();
    }
}

if (! function_exists('getMentionsFromContent')) {
    function getMentionsFromContent($content)
    {
        preg_match_all(config('shaun_core.regex.mention'), $content, $matches);
        
        $results = ! empty($matches[0]) ? array_unique($matches[0]) : [];
        
        return collect($results)->sortByDesc(function ($result, $key) {
            return strlen($result);
        })->toArray();
    }
}

if (! function_exists('findByTypeId')) {
    function findByTypeId($type, $typeId)
    {
        $class = ModelMap::getModel($type);
        if (! $class) {
            return false;
        }

        return $class::findByField('id', $typeId);
    }
}

if ( ! function_exists('putPermanentEnv')) {
    function putPermanentEnv($key, $value)
    {
        $value = str_replace('"', '', $value);
        $value = '"'.$value.'"';
        $path = app()->environmentFilePath();
        if (! file_exists($path) || ! is_writable($path)) {
            return;
        }

        $escaped = preg_quote('='.env($key), '/');

        file_put_contents($path, preg_replace(
            "/^{$key}{$escaped}/m",
           "{$key}={$value}",
           file_get_contents($path)
        ));
    }
}

if ( ! function_exists('checkNextPage')) {
    function checkNextPage($count, $current, $page, $limit = 0)
    {
        if (! $limit) {
            $limit = setting('feature.item_per_page');
        }

        return (($limit * ($page - 1)) + $current) < $count;
    }
}

if ( ! function_exists('getDeleteUser')) {
    function getDeleteUser()
    {
        $user = new User();
        $user->name = __('Deleted user');
        return $user;
    }
}

if ( ! function_exists('getUserIncludeDelete')) {
    function getUserIncludeDelete($user)
    {
        if ($user) {
            return $user;
        }
        return getDeleteUser();
    }
}

if ( ! function_exists('getDeletePage')) {
    function getDeletePage()
    {
        $user = new User();
        $user->name = __('Deleted page');
        $user->is_page = true;
        return $user;
    }
}

if ( ! function_exists('shaunBroadcast')) {
    function shaunBroadcast($event)
    {
        if (setting('broadcast.enable')) {
            try {
                event($event);
            } catch (Exception $e) {

            }
        }
    }
}

if ( ! function_exists('getTimezoneList')) {
    function getTimezoneList()
    {
        return [
            'Etc/GMT-12'=>'(UTC-12) Eniwetok, Kwajalein',
            'Pacific/Samoa'=>'(UTC-11) Midway Island, Samoa',
            'Pacific/Honolulu'=>'(UTC-10) Hawaii (US)',
            'America/Anchorage'=>'(UTC-9)  Alaska (US & Canada)',
            'America/Los_Angeles'=>'(UTC-8) Pacific Time (US & Canada)',
            'America/Creston'=>'(UTC-7) Mountain Time (US & Canada)',
            'America/Guatemala'=>'(UTC-6) Central Time (US & Canada)',
            'America/Cancun'=>'(UTC-5) Eastern Time (US & Canada)',
            'America/Halifax'=>'(UTC-4)  Atlantic Time (Canada)',            
            'America/St_Johns'=>'(UTC-3:30) Canada/Newfoundland',
            'America/Araguaina'=>'(UTC-3) Brasilia, Buenos Aires, Georgetown',
            'Atlantic/South_Georgia'=>'(UTC-2) Mid-Atlantic',
            'Atlantic/Azores'=>'(UTC-1) Azores, Cape Verde Is.',
            'Europe/London'=>'Greenwich Mean Time (Lisbon, London)',
            'Europe/Berlin'=>'(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
            'Europe/Athens'=>'(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
            'Europe/Moscow'=>'(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
            'Asia/Tehran'=>'(UTC+3:30) Tehran',
            'Asia/Dubai'=>'(UTC+4) Abu Dhabi, Kazan, Muscat',
            'Asia/Kabul'=>'(UTC+4:30) Kabul',
            'Asia/Yekaterinburg'=>'(UTC+5) Islamabad, Karachi, Tashkent',
            'Asia/Kolkata'=>'(UTC+5:30) Bombay, Calcutta, New Delhi',
            'Asia/Kathmandu'=>'(UTC+5:45) Nepal',
            'Asia/Omsk'=>'(UTC+6) Almaty, Dhaka',
            'Indian/Cocos'=>'(UTC+6:30) Cocos Islands, Yangon',
            'Asia/Krasnoyarsk'=>'(UTC+7) Bangkok, Jakarta, Hanoi',
            'Asia/Hong_Kong'=>'(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
            'Asia/Tokyo'=>'(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
            'Australia/Adelaide'=>'(UTC+9:30) Adelaide, Darwin',
            'Australia/Sydney'=>'(UTC+10) Brisbane, Melbourne, Sydney, Guam',
            'Asia/Magadan'=>'(UTC+11) Magadan, Solomon Is., New Caledonia',
            'Pacific/Auckland'=>'(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
        ];
    }
}

if ( ! function_exists('getHashUnsubscribeFromEmail')) {
    function getHashUnsubscribeFromEmail($email)
    {
        return md5(config('app.name').$email);
    }
}

if (!function_exists('checkUsernameBan')) {
    function checkUsernameBan($userName)
    {
        if (setting('spam.username_ban')) {
            $userNameBans = array_map('trim', explode(',', setting('spam.username_ban')));
            return in_array($userName, $userNameBans);
        }

        return false;
    }
}

if (!function_exists('checkEmailBan')) {
    function checkEmailBan($email)
    {
        if (setting('spam.email_ban')) {
            $emailBans = array_map('trim', explode(',', setting('spam.email_ban')));
            foreach ($emailBans as $emailBan)
            {
                if (false === strpos($emailBan, '*')) {
                    if (strtolower($email) == $emailBan) {
                        return true;
                        break;
                    }
                } else {
                    $pregExpr = preg_quote($emailBan, '/');
                    $pregExpr = str_replace('\\*', '.*', $pregExpr);
                    $pregExpr = '/^' . $pregExpr . '$/i';
                    if (preg_match($pregExpr, $email)) {
                        return true;
                        break;
                    }
                }
            }
        }

        return false;
    }
}

if (!function_exists('setAppCookie')) {
    function setAppCookie($response, $name, $value, $time)
    {
        $parse = parse_url(config('app.url'));
        if (empty($parse['path'])) {
            $parse['path'] = null;
        }
        return $response->cookie($name, $value, $time, $parse['path']);
    }
}

if (!function_exists('getClientOptions')) {
    function getClientOptions()
    {
        $userAgent = ! empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if ( !$userAgent || app()->runningInConsole() ){
            $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36';
        }

        $options = [
            'headers' => [
                'User-Agent' => $userAgent
            ],
            'timeout' => config('shaun_core.core.http_timeout'),
            'curl' => [
                CURLOPT_TIMEOUT => config('shaun_core.core.http_timeout')
            ]
        ];

        if (defined('CURLOPT_HTTP09_ALLOWED')) {			
            $options['curl'][CURLOPT_HTTP09_ALLOWED] =true;
        }

        return $options;
    }
}

if (!function_exists('getYoutubeEmbedUrl')) {
    function getYoutubeEmbedUrl($url)
    {
        $regex = '#^(?:https?://|//)?(?:www\.|m\.|.+\.)?(?:youtu\.be/|youtube\.com/(?:embed/|v/|shorts/|feeds/api/videos/|watch\?v=|watch\?.+&v=))([\w-]{11})(?![\w-])#';
        preg_match($regex, $url, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }
}

if (!function_exists('getServerLanguagePath')) {
    function getServerLanguagePath($key)
    {
        return base_path('lang').'/'.$key.'.json';
    }
}

if (!function_exists('getClientLanguagePath')) {
    function getClientLanguagePath($key)
    {
        return base_path('public/locales').'/'.$key.'.json';
    }
}

if (!function_exists('getServerLanguageArray')) {
    function getServerLanguageArray($key)
    {
        return json_decode(file_get_contents(getServerLanguagePath($key)),true);
    }
}

if (!function_exists('getClientLanguageArray')) {
    function getClientLanguageArray($key)
    {
        return json_decode(file_get_contents(getClientLanguagePath($key)),true);
    }
}

if (!function_exists('writeFileLanguageJson')) {
    function writeFileLanguageJson($fileName, $array)
    {
        file_put_contents($fileName, json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}


if (!function_exists('getLanguageArray')) {
    function getLanguageArray($key)
    {
        $serverArray = getServerLanguageArray($key);
        $clientArray = getClientLanguageArray($key);

        return array_merge($serverArray, $clientArray);
    }
}

if (! function_exists('clearClientCache')) {
    function clearClientCache()
    {
        $setting = SettingModel::where('key', 'site.cache_number')->first();
        $setting->update([
            'value' => $setting->value + 1
        ]);
    }
}

if (! function_exists('clearAllCache')) {
    function clearAllCache()
    {
        clearClientCache();

        Cache::flush();

        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
    }
}

if (! function_exists('getHashtagFromUserHashtag')) {
    function getHashtagFromUserHashtag($hashtags)
    {        
        $hashtags = Str::of($hashtags)->split('/[\s,]+/');
        return $hashtags->join(' ');
    }
}

if (! function_exists('getPathForDownload')) {
    function getPathForDownload($url)
    {        
        $path = str_replace(setting('site.url').'/', '', $url);
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($url);
            return ltrim($path['path'],'/');
        }

        return $path;
    }
}

if (! function_exists('getFFMpeg')) {
    function getFFMpeg()
    {        
        $ffmpegPath = setting('feature.ffmpeg_path');        
        $ffprobePath = Str::replaceLast('ffmpeg', 'ffprobe', $ffmpegPath);
        try {
            $params = [
                'ffmpeg.binaries'  => $ffmpegPath,
                'ffprobe.binaries' => $ffprobePath,
            ];

            if (env('SERVER_FFMPEG_THREADS')) {
                $params['ffmpeg.threads'] = env('SERVER_FFMPEG_THREADS');
            } else if (env('CLOUD_ENABLE')) {
                $params['ffmpeg.threads'] = 1;
            }
            return FFMpeg\FFMpeg::create($params);
        } catch (Exception $e) {

        }
        return null;
    }
}

if (! function_exists('exportDownloadDataFile')) {
    function exportDownloadDataFile($userId, $url)
    {
        $path = getPathForDownload($url);
        $file = storage_path($path);
        $filePublic = public_path($path);
        $filePathSave = storage_path('download_data/'. $userId);
        if (file_exists($file)) {
            appCopy($file, $filePathSave.DIRECTORY_SEPARATOR.$path);
        } else if (file_exists($filePublic)) {
            appCopy($filePublic, $filePathSave.DIRECTORY_SEPARATOR.$path);
        } else {
            $content = file_get_contents($url);
			$pathFull = $filePathSave.DIRECTORY_SEPARATOR.$path;
			$folder = dirname($pathFull);
			if (!file_exists($folder)) {
				mkdir($folder, 0777, true);
			}
            file_put_contents($pathFull, $content);
        }
    }
}

if (! function_exists('getMaxUploadFilesServer')) {
    function getMaxUploadFilesServer()
    {
        $val = ini_get('upload_max_filesize');

        $size = trim($val);

        #
        # Separate the value from the metric(i.e MB, GB, KB)
        #
        preg_match('/([0-9]+)[\s]*([a-zA-Z]+)/', $size, $matches);

        $value = (isset($matches[1])) ? $matches[1] : 0;
        $metric = (isset($matches[2])) ? strtolower($matches[2]) : 'b';

        #
        # Result of $value multiplied by the matched case
        # Note: (1024 ** 2) is same as (1024 * 1024) or pow(1024, 2)
        #
        $value *= match ($metric) {
            'k', 'kb' => 1024,
            'm', 'mb' => (1024 ** 2),
            'g', 'gb' => (1024 ** 3),
            't', 'tb' =>  (1024 ** 4),
            default => 0
        };

        return (int)$value / 1024;
    }
}

if (! function_exists('getMaxUploadFileSize')) {
    function getMaxUploadFileSize()
    {
        $maxUpload = setting('feature.file_max_upload');
        if ($maxUpload && is_numeric($maxUpload)) {
            return setting('feature.file_max_upload')*1024;
        }

        return getMaxUploadFilesServer();
    }
}

if (! function_exists('appCopy')) {
    function appCopy($from, $to) 
    {
        $path = pathinfo($to);
        if (!file_exists($path['dirname'])) {
            mkdir($path['dirname'], 0777, true);
        }

        copy($from, $to);
    }
}

if (! function_exists('deleteFolder')) {
    function deleteFolder($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                deleteFolder($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}

if (! function_exists('makeTextForHeader')) {
    function makeTextForHeader($input, $addslashes = true)
    {
        $input = strip_tags($input);
        
        return $addslashes ? addslashes($input): $input;
    }
}

if (! function_exists('getUserLanguage')) {
    function getUserLanguage($user)
    {
        $languages = Language::getAll(false);
        $keyLanguages = $languages->filter(function ($value, $key) {
            return $value->is_active;
        })->pluck('name', 'key')->all();
        
        $defaultLanguage = $languages->first(function ($value, $key) {
            return $value->is_default;
        });

        if ($user->language && array_key_exists($user->language, $keyLanguages)) {
            return $user->language; 
        }

        return $defaultLanguage->key;
    }
}

if (! function_exists('getThemeSettingDefault')) {
    function getThemeSettingDefault()
    {
        return [
            'main' => [
                'body_background_color' => '#f5f5f5',
                'border_divider_color' => '#E0E0E0',
                'main_text_color' => '#333333',
                'sub_text_color' => '#828282',
                'link_text_color' => '#66C7F4',
                'link_hover_color' => '#66C7F4',
                'body_background_loading_color' => '#f0f4ff'
            ],
            'widget' => [
                'widget_background_color' => '#fff',
                'widget_border_color' => '#fff',
                'widget_title_color' => '#333',
                'widget_view_all_text_color' => '#66c7f4',
                'widget_text_color' => '#333'
            ],
            'header' => [
                'header_background_color' => '#f5f5f5',
                'header_border_scrolling_color' => '#f2f2f2',
                'header_icon_color' => '#333333',
                'header_icon_badge_background_color' => '#eb3349',
                'header_icon_badge_text_color' => '#fff'
            ],
            'footer' => [
                'footer_text_color' => '#828282',
                'footer_border_color' => '#e0e0e0',
                'footer_link_color' => '#66C7F4',
                'footer_link_hover_color' => '#66C7F4',
                'footer_menu_item_text_color' => '#828282',
                'footer_menu_item_hover_color' => '#828282',
            ],
            'main_content' => [
                'main_content_background_color' => '#fff',
                'main_content_border_color' => '#fff',
                'main_content_menu_border_color' => '#e0e0e0',
                'main_content_menu_background_color' => 'transparent',
                'main_content_menu_item_text_color' => '#333',
                'main_content_menu_item_border_color' => 'transparent',
                'main_content_menu_item_text_active_color' => '#66C7F4',
                'main_content_menu_item_border_active_color' => '#66C7F4',
            ],
            'list_item' => [
                'list_items_title_text_color' => '#333',
                'list_items_sub_text_color' => '#828282',
                'list_items_button' => '#66C7F4',
                'list_items_button_hover_color' => '#66C7F4',
                'list_box_items_background_color' => 'transparent',
                'list_box_items_border_color' => '#bdbdbd',
                'list_box_items_text_color' => '#828282'
            ],
            'grid_item' => [
                'grid_items_background_color' => '#fff',
                'grid_items_border_color' => '#e0e0e0',
                'grid_items_title_color' => '#333',
                'grid_items_sub_color' => '#828282'
            ],
            'emoji' => [
                'emoji_icon_color' => '#333',
                'emoji_box_background_color' => '#fff',
                'emoji_box_text_color' => '#9ca3af',
            ],
            'mobile' => [
                'header_mobile_background_color' => '#fff',
                'header_mobile_icon_color' => '#333333',
                'footer_mobile_background_color' => '#fff',
                'footer_mobile_icon_color' => '#333333',
                'footer_mobile_icon_active_color' => '#66c7f4',
                'footer_mobile_create_icon_color' => '#eb3349',
                'footer_icon_badge_background_color' => '#eb3349',
                'footer_icon_badge_text_color' => '#fff'
            ],
            'modal' => [                
                'modal_background_color' => '#fff',
                'modal_text_color' => '#333',
                'modal_icon_color' => '#333',
                'modal_icon_background_color' => '#f8fafc'
            ],
            'profile' => [
                'profile_header_background_color' => '#fff',
                'profile_header_name_text_color' => '#333',
                'profile_header_user_name_text_color' => '#828282',
                'profile_header_count_text_color' => '#333'
            ],
            'global_search' => [                      
                'global_search_background_color' => '#fff',
                'global_search_border_color' => '#e0e0e0',
                'global_search_icon_color' => '#828282',
                'global_search_text_color' => '#333333',
                'global_search_placeholder_color' => '#828282',
                'global_search_suggestion_background' => '#fff',
                'global_search_suggestion_icon' => '#333',
                'global_search_suggestion_text' => '#333',
                'global_search_result_title' => '#333',
                'global_search_result_sub' => '#828282',
            ],
            'story' => [
                'create_box_background_color' => '#fff',
                'create_box_border_color' => '#e0e0e0',
                'create_btn_color' => '#66C7F4',
                'create_box_text_color' => '#333',
                'story_progress_bar_color' => '#66C7F4',
                'story_remaining_bar_color' => '#D9D9D9',
                'story_name_color' => '#fff',
                'story_date_color' => '#fff',
                'story_icon_color' => '#fff',
                'story_viewer_color' => '#fff'
            ],
            'notification' => [
                'notification_popup_color' => '#fff',
                'notification_background' => '#fff',
                'notification_name_color' => '#333333',
                'notification_message_color' => '#333333',
                'notification_date_color' => '#828282',
                'notification_active_background' => '#fff',
                'notification_active_name_color' => '#333333',
                'notification_active_message_color' => '#333333',
                'notification_active_date_color' => '#66C7F4',
                'notification_active_dot_color' => '#66C7F4',
                'notification_view_all_color' => '#66C7F4'
            ],
            'status' => [
                'status_post_modal_background_color' => '#fff',
                'status_post_modal_border_color' => '#fff',
                'status_post_modal_close_icon_color' => '#333333',
                'status_post_modal_user_text_color' => '#333333',
                'status_post_modal_textarea_color' => '#333333',
                'status_post_modal_textarea_placeholder_color' => '#828282',
                'status_post_modal_background_add_icon_color' => '#fff',
                'status_post_modal_border_add_icon_color' => '#e0e0e0',
                'status_post_modal_add_icon_color' => '#333',
                'status_post_privacy_button_background_color' => '#e0e0e0',
                'status_post_privacy_button_text_color' => '#333',
                'status_post_modal_action_background_color' => '#fff',
                'status_post_modal_action_border_color' => '#e0e0e0',
                'status_post_modal_action_icon_color' => '#333',
                'status_post_modal_action_button_background_color' => '#f4f4f4',
                'status_post_modal_action_button_text_color' => '#333',
                'status_post_modal_action_button_icon_color' => '#333',
            ],
            'more' => [
                'loading_icon_color' => '#66C7F4',
                'table_header_background_color' => '#e0e0e0',
                'table_header_text_color' => '#333',
                'table_body_background_color' => '#fff',
                'table_body_border_color' => '#e0e0e0',
                'table_body_text_color' => '#333',
                'options_modal_background_color' => '#fff',
                'options_modal_border_color' => '#f2f2f2',
                'options_modal_text_color' => '#dc2626',
                'options_modal_sub_text_color' => '#333',
                'dropdown_options_background_color' => '#fff',
                'dropdown_options_border_color' => '#e0e0e0',
                'dropdown_options_text_color' => '#333',
                'dropdown_options_background_hover' => '#f3f4f6',
                'dropdown_options_text_color_hover' => '#333',
                'dropdown_options_background_active' => 'transparent',
                'dropdown_options_text_color_active' => '#66C7F4',
                'tab_list_item_background_color' => '#e0e0e0',
                'tab_list_item_text_color' => '#333',
                'tab_list_item_active_background_color' => '#66c7f4',
                'tab_list_item_active_text_color' => '#fff',
                'tooltip_background_color' => '#fff',
                'tooltip_border_color' => '#e0e0e0',
                'tooltip_text_color' => '#333',
                'progress_track_background' => '#e2e8f0',
                'progress_bar_background' => '#66c7f4',
                'progress_bar_text' => '#fff'
            ],
            'sidebar' => [
                'sidebar_background_color' => '#fff',
                'sidebar_box_background_color' => '#fcfcfc',
                'sidebar_box_border_color' => '#f4f4f4',
                'sidebar_name_text_color' => '#333',
                'sidebar_sub_text_color' => '#828282',
                'sidebar_link_text_color' => '#66c7f4',
                'sidebar_icon_color' => '#333',
                'sidebar_menu_title_color' => '#828282',
                'sidebar_menu_item_background_color' => '#fff',
                'sidebar_menu_item_text_color' => '#333',
                'sidebar_menu_item_icon_color' => '#333',
                'sidebar_menu_item_background_hover_color' => '#fff',
                'sidebar_menu_item_text_hover_color' => '#66c7f4',
                'sidebar_menu_item_icon_hover_color' => '#66c7f4',
                'sidebar_menu_item_background_active_color' => '#F1FBFF',
                'sidebar_menu_item_text_active_color' => '#66c7f4',
                'sidebar_menu_item_icon_active_color' => '#66c7f4',
            ],
            'comment' => [
                'comment_username_color' => '#333',
                'comment_content_color' => '#333',
                'comment_date_color' => '#828282',
                'comment_icon_color' => '#333',
                'comment_icon_active_color' => '#eb3349',
                'comment_view_all_color' => '#828282',
                'reply_username_color' => '#333',
                'reply_content_color' => '#333',
                'reply_date_color' => '#828282',
                'reply_icon_color' => '#333',
                'reply_icon_active_color' => '#eb3349',
                'reply_status_background_color' => '#e0e0e0',
                'reply_status_text_color' => '#333',
                'reply_status_icon_color' => '#333'
            ],
            'feed' => [
                'feed_background_color' => '#fff',
                'feed_background_hover_color' => '#00000008',
                'feed_border_color' => '#fff',
                'feed_border_hover_color' => '#00000008',
                'feed_header_info_title_text_color' => '#333',
                'feed_header_info_date_text_color' => '#828282',
                'feed_dropdown_icon_color' => '#828282',
                'feed_content_text_color' => '#333',
                'feed_poll_background_color' => '#e0e0e0',
                'feed_poll_text_color' => '#333',
                'feed_selected_poll_background_color' => '#66c7f4',
                'feed_selected_poll_text_color' => '#333',
                'feed_poll_sub_text_color' => '#828282',
                'feed_content_share_background_color' => '#f2f2f2',
                'feed_slider_dot_background_color' => '#D9D9D9',
                'feed_slider_dot_active_background_color' => '#66C7F4',
                'feed_like_text_color' => '#333',
                'feed_action_border_color' => '#e0e0e0',
                'feed_action_item_color' => '#333',
                'feed_like_active_color' => '#eb3349',
                'feed_bookmark_active_color' => '#66C7F4',
                'feed_comment_form_border_color' => '#e0e0e0',
                'feed_comment_form_background_color' => '#f4f4f4',
                'feed_comment_form_placeholder_color' => '#828282',
                'feed_comment_form_text_color' => '#333',
                'feed_comment_form_icon_color' => '#333',
                'feed_comment_form_button_color' => '#66C7F4',
                'feed_notifications_button_bg_color' => '#66C7F4',
                'feed_notifications_button_text_color' => '#fff'
            ],
            'form' => [
                'button_primary_background_color' => '#66C7F4',
                'button_primary_border_color' => '#66C7F4',
                'button_primary_text_color' => '#fff',
                'button_secondary_background_color' => '#AAAAAA',
                'button_secondary_border_color' => '#AAAAAA',
                'button_secondary_text_color' => '#fff',
                'button_outlined_background_color' => 'transparent',
                'button_outlined_border_color' => '#66C7F4',
                'button_outlined_text_color' => '#66C7F4',
                'button_secondary_outlined_background_color' => 'transparent',
                'button_secondary_outlined_border_color' => '#BDBDBD',
                'button_secondary_outlined_text_color' => '#333',
                'button_transparent_background_color' => 'transparent',
                'button_transparent_border_color' => 'transparent',
                'button_transparent_text_color' => '#66C7F4',
                'button_danger_background_color' => '#EB5757',
                'button_danger_border_color' => '#EB5757',
                'button_danger_text_color' => '#fff',
                'button_danger_outlined_background_color' => 'transparent',
                'button_danger_outlined_border_color' => '#EB5757',
                'button_danger_outlined_text_color' => '#EB5757',
                'button_warning_background_color' => '#eea236',
                'button_warning_border_color' => '#eea236',
                'button_warning_text_color' => '#fff',
                'button_success_background_color' => '#00b901',
                'button_success_border_color' => '#00b901',
                'button_success_text_color' => '#fff',
                'button_info_background_color' => '#46b8da',
                'button_info_border_color' => '#46b8da',
                'button_info_text_color' => '#fff',
                'input_background_color' => '#fff',
                'input_border_color' => '#E0E0E0',
                'input_text_color' => '#333',
                'input_icon_color' => '#828282',
                'input_placeholder_color' => '#828282',
                'select_background_color' => '#fff',
                'select_border_color' => '#E0E0E0',
                'select_text_color' => '#333',
                'select_text_color_hover' => '#495057',
                'select_background_color_hover' => '#e9ecef',
                'select_text_color_selected' => '#66c7f4',
                'select_background_color_selected' => '#f1fbff',
                'switch_color' => '#ced4da',
                'switch_active_color' => '#66C7F4',
                'radio_button_color' => '#e0e0e0',
                'radio_button_active_color' => '#66C7F4',
                'checkbox_button_color' => '#e0e0e0',
                'checkbox_button_active_color' => '#66C7F4',
                'selected_background_color' => '#F1FBFF',
                'selected_text_color' => '#333333',
                'selected_icon_color' => '#66c7f4'
            ],
            'chat' => [
                'chat_title_color' => '#333',
                'chat_user_name_color' => '#333',
                'chat_options_icon_color' => '#828282',
                'chat_date_color' => '#828282',
                'room_search_background' => 'transparent',
                'room_search_border' => '#e0e0e0',
                'room_search_placeholder' => '#828282',
                'room_search_text' => '#333',
                'room_item_background' => 'transparent',
                'room_item_name' => '#333333',
                'room_item_date' => '#828282',
                'room_item_dropdown_icon' => '#828282',
                'room_item_background_hover' => '#f2f2f2',
                'room_item_name_hover' => '#333333',
                'room_item_date_hover' => '#828282',
                'room_item_background_active' => '#F1FBFF',
                'room_item_name_active' => '#333333',
                'room_item_date_active' => '#828282',
                'room_item_dot_active' => '#66C7F4',
                'message_item_background' => '#f2f2f2',
                'message_item_border' => '#f2f2f2',
                'message_item_color' => '#333',
                'owner_message_item_background' => '#66C7F4',
                'owner_message_item_border' => '#66C7F4',
                'owner_message_item_color' => '#fff',
                'waiting_request_background' => '#f2f2f2',
                'waiting_request_border' => '#e0e0e0',
                'waiting_request_text' => '#333',
                'accept_btn_background' => 'transparent',
                'accept_btn_border' => '#e0e0e0',
                'accept_btn_text' => '#27ae60',
                'delete_btn_background' => 'transparent',
                'delete_btn_border' => '#e0e0e0',
                'delete_btn_text' => '#eb3349',
                'message_form_background_color' => '#fff',
                'message_form_placeholder_color' => '#828282',
                'message_form_text_color' => '#333',
                'send_message_btn' => '#66C7F4',
                'scroll_bottom_btn_background' => '#fff',
                'scroll_bottom_btn_color' => '#66C7F4',
                'chat_bubble_background_color' => '#66c7f4',
                'chat_bubble_icon_color' => '#fff'
            ],
            'cookies_warning' => [
                'cookies_warning_background_color' => '#fff',
                'cookies_warning_text_color' => '#333333',
            ],
            'mention' => [
                'mention_background_color' => '#fff',
                'mention_title_color' => '#333',
                'mention_sub_color' => '#828282',
                'mention_background_active' => '#D9D9D9'
            ],
            'fetch_link' => [
                'fetch_link_close_icon_color' => '#66c7f4',
                'fetch_link_background_color' => '#66c7f4',
                'fetch_link_text_color' => '#fff',
            ],
            'video_player' => [
                'video_player_progress_color' => '#66c7f4',
                'video_player_volume' => '#66c7f4'
            ],
            'pages' => [
                'pages_switch_color' => '#66c7f4'
            ],
            'vibb' => [
                'vibb_menu_color' => '#fff',
                'vibb_menu_active_color' => '#66c7f4',
                'vibb_menu_active_border_color' => '#66c7f4',
                'vibb_menu_icon_color' => '#fff',
                'vibb_action_background_color' => '#fff',
                'vibb_action_icon_color' => '#333',
                'vibb_action_text_color' => '#fff',
                'vibb_icon_active_color' => '#eb3349',
                'vibb_icon_bookmark_active_color' => '#66C7F4',
            ],
            'tabs_menu' => [
                'tabs_menu_item_background_color' => '#fff',
                'tabs_menu_item_icon_color' => '#333',
                'tabs_menu_item_text_color' => '#333',
                'tabs_menu_item_active_background_color' => '#66c7f4',
                'tabs_menu_item_active_icon_color' => '#fff',
                'tabs_menu_item_active_text_color' => '#fff',
            ],
            'invites' => [
                'invites_button_background_color' => '#e0e0e0',
                'invites_button_border_color' => '#bdbdbd',
                'invites_button_text_color' => '#333333',
                'invites_button_icon_color' => '#66c7f4'
            ],
            'badges' => [
                'ads_badge_background_color' => '#e0e0e0',
                'ads_badge_text_color' => '#333',
                'label_badge_background_color' => '#ccf0ff',
                'label_badge_text_color' => '#66c7f4',
                'pinned_badge_background_color' => '#ccf0ff',
                'pinned_badge_text_color' => '#333'
            ]
        ];
    }
}

if (! function_exists('getThemeSettingDarkDefault')) {
    function getThemeSettingDarkDefault()
    {
        return [
            'main' => [
                'body_background_color' => '#111827',
                'border_divider_color' => '#ffffff26',
                'main_text_color' => '#fff',
                'sub_text_color' => '#94a3b8',
                'link_text_color' => '#2d88ff',
                'link_hover_color' => '#2d88ff',
                'body_background_loading_color' => '#334155'
            ],
            'widget' => [
                'widget_background_color' => '#1e293b',
                'widget_border_color' => '#0f172a',
                'widget_title_color' => '#fff',
                'widget_view_all_text_color' => '#2d88ff',
                'widget_text_color' => '#fff'
            ],
            'header' => [
                'header_background_color' => '#111827',
                'header_border_scrolling_color' => '#1e293b',
                'header_icon_color' => '#fff',
                'header_icon_badge_background_color' => '#eb3349',
                'header_icon_badge_text_color' => '#fff'
            ],
            'footer' => [
                'footer_text_color' => '#d1d5db',
                'footer_border_color' => '#ffffff26',
                'footer_link_color' => '#2d88ff',
                'footer_link_hover_color' => '#2d88ff',
                'footer_menu_item_text_color' => '#d1d5db',
                'footer_menu_item_hover_color' => '#d1d5db',
            ],
            'main_content' => [
                'main_content_background_color' => '#1e293b',
                'main_content_border_color' => '#1e293b',
                'main_content_menu_border_color' => '#ffffff26',
                'main_content_menu_background_color' => 'transparent',
                'main_content_menu_item_text_color' => '#fff',
                'main_content_menu_item_border_color' => 'transparent',
                'main_content_menu_item_text_active_color' => '#2d88ff',
                'main_content_menu_item_border_active_color' => '#2d88ff',
            ],
            'list_item' => [
                'list_items_title_text_color' => '#fff',
                'list_items_sub_text_color' => '#94a3b8',
                'list_items_button' => '#2d88ff',
                'list_items_button_hover_color' => '#2d88ff',
                'list_box_items_background_color' => 'transparent',
                'list_box_items_border_color' => '#ffffff4d',
                'list_box_items_text_color' => '#94a3b8'
            ],
            'grid_item' => [
                'grid_items_background_color' => '#1e293b',
                'grid_items_border_color' => '#ffffff26',
                'grid_items_title_color' => '#fff',
                'grid_items_sub_color' => '#94a3b8'
            ],
            'emoji' => [
                'emoji_icon_color' => '#fff',
                'emoji_box_background_color' => '#1e293b',
                'emoji_box_text_color' => '#9ca3af',
            ],
            'mobile' => [
                'header_mobile_background_color' => '#1e293b',
                'header_mobile_icon_color' => '#fff',
                'footer_mobile_background_color' => '#1e293b',
                'footer_mobile_icon_color' => '#fff',
                'footer_mobile_icon_active_color' => '#2d88ff',
                'footer_mobile_create_icon_color' => '#eb3349',
                'footer_icon_badge_background_color' => '#eb3349',
                'footer_icon_badge_text_color' => '#fff'
            ],
            'modal' => [                
                'modal_background_color' => '#1e293b',
                'modal_text_color' => '#fff',
                'modal_icon_color' => '#fff',
                'modal_icon_background_color' => '#f8fafc'
            ],
            'profile' => [
                'profile_header_background_color' => '#1e293b',
                'profile_header_name_text_color' => '#fff',
                'profile_header_user_name_text_color' => '#94a3b8',
                'profile_header_count_text_color' => '#fff'
            ],
            'global_search' => [                      
                'global_search_background_color' => '#1e293b',
                'global_search_border_color' => '#ffffff26',
                'global_search_icon_color' => '#94a3b8',
                'global_search_text_color' => '#fff',
                'global_search_placeholder_color' => '#cbd5e1',
                'global_search_suggestion_background' => '#1e293b',
                'global_search_suggestion_icon' => '#fff',
                'global_search_suggestion_text' => '#fff',
                'global_search_result_title' => '#fff',
                'global_search_result_sub' => '#94a3b8',
            ],
            'story' => [
                'create_box_background_color' => '#1e293b',
                'create_box_border_color' => '#ffffff26',
                'create_btn_color' => '#2d88ff',
                'create_box_text_color' => '#fff',
                'story_progress_bar_color' => '#fff',
                'story_remaining_bar_color' => '#9ca3af',
                'story_name_color' => '#fff',
                'story_date_color' => '#fff',
                'story_icon_color' => '#fff',
                'story_viewer_color' => '#fff'
            ],
            'notification' => [
                'notification_popup_color' => '#1e293b',
                'notification_background' => '#1e293b',
                'notification_name_color' => '#fff',
                'notification_message_color' => '#fff',
                'notification_date_color' => '#94a3b8',
                'notification_active_background' => '#1e293b',
                'notification_active_name_color' => '#fff',
                'notification_active_message_color' => '#fff',
                'notification_active_date_color' => '#2d88ff',
                'notification_active_dot_color' => '#2d88ff',
                'notification_view_all_color' => '#2d88ff'
            ],
            'status' => [
                'status_post_modal_background_color' => '#1e293b',
                'status_post_modal_border_color' => '#1e293b',
                'status_post_modal_close_icon_color' => '#fff',
                'status_post_modal_user_text_color' => '#fff',
                'status_post_modal_textarea_color' => '#fff',
                'status_post_modal_textarea_placeholder_color' => '#94a3b8',
                'status_post_modal_background_add_icon_color' => '#1e293b',
                'status_post_modal_border_add_icon_color' => '#ffffff26',
                'status_post_modal_add_icon_color' => '#fff',
                'status_post_privacy_button_background_color' => '#334155',
                'status_post_privacy_button_text_color' => '#fff',
                'status_post_modal_action_background_color' => '#1e293b',
                'status_post_modal_action_border_color' => '#ffffff26',
                'status_post_modal_action_icon_color' => '#fff',
                'status_post_modal_action_button_background_color' => '#334155',
                'status_post_modal_action_button_text_color' => '#fff',
                'status_post_modal_action_button_icon_color' => '#fff',
            ],
            'more' => [
                'loading_icon_color' => '#2d88ff',
                'table_header_background_color' => '#323f4f',
                'table_header_text_color' => '#fff',
                'table_body_background_color' => '#1e293b',
                'table_body_border_color' => '#ffffff26',
                'table_body_text_color' => '#fff',
                'options_modal_background_color' => '#1e293b',
                'options_modal_border_color' => '#ffffff26',
                'options_modal_text_color' => '#dc2626',
                'options_modal_sub_text_color' => '#fff',
                'dropdown_options_background_color' => '#334155',
                'dropdown_options_border_color' => '#ffffff26',
                'dropdown_options_text_color' => '#e5e7eb',
                'dropdown_options_background_hover' => '#4b5563',
                'dropdown_options_text_color_hover' => '#fff',
                'dropdown_options_background_active' => 'transparent',
                'dropdown_options_text_color_active' => '#2d88ff',
                'tab_list_item_background_color' => '#334155',
                'tab_list_item_text_color' => '#fff',
                'tab_list_item_active_background_color' => '#2d88ff',
                'tab_list_item_active_text_color' => '#fff',
                'tooltip_background_color' => '#334155',
                'tooltip_border_color' => '#ffffff26',
                'tooltip_text_color' => '#fff',
                'progress_track_background' => '#e2e8f0',
                'progress_bar_background' => '#2d88ff',
                'progress_bar_text' => '#fff'
            ],
            'sidebar' => [
                'sidebar_background_color' => '#1e293b',
                'sidebar_box_background_color' => '#334155',
                'sidebar_box_border_color' => '#ffffff26',
                'sidebar_name_text_color' => '#fff',
                'sidebar_sub_text_color' => '#94a3b8',
                'sidebar_link_text_color' => '#2d88ff',
                'sidebar_icon_color' => '#fff',
                'sidebar_menu_title_color' => '#94a3b8',
                'sidebar_menu_item_background_color' => '#1e293b',
                'sidebar_menu_item_text_color' => '#d1d5db',
                'sidebar_menu_item_icon_color' => '#d1d5db',
                'sidebar_menu_item_background_hover_color' => '#1e293b',
                'sidebar_menu_item_text_hover_color' => '#2d88ff',
                'sidebar_menu_item_icon_hover_color' => '#2d88ff',
                'sidebar_menu_item_background_active_color' => '#323f4f',
                'sidebar_menu_item_text_active_color' => '#2d88ff',
                'sidebar_menu_item_icon_active_color' => '#2d88ff',
            ],
            'comment' => [
                'comment_username_color' => '#fff',
                'comment_content_color' => '#fff',
                'comment_date_color' => '#94a3b8',
                'comment_icon_color' => '#fff',
                'comment_icon_active_color' => '#eb3349',
                'comment_view_all_color' => '#94a3b8',
                'reply_username_color' => '#fff',
                'reply_content_color' => '#fff',
                'reply_date_color' => '#94a3b8',
                'reply_icon_color' => '#fff',
                'reply_icon_active_color' => '#eb3349',
                'reply_status_background_color' => '#323f4f',
                'reply_status_text_color' => '#fff',
                'reply_status_icon_color' => '#fff'
            ],
            'feed' => [
                'feed_background_color' => '#1e293b',
                'feed_background_hover_color' => '#ffffff1a',
                'feed_border_color' => '#1e293b',
                'feed_border_hover_color' => '#ffffff1a',
                'feed_header_info_title_text_color' => '#fff',
                'feed_header_info_date_text_color' => '#94a3b8',
                'feed_dropdown_icon_color' => '#94a3b8',
                'feed_content_text_color' => '#fff',
                'feed_poll_background_color' => '#64748b',
                'feed_poll_text_color' => '#fff',
                'feed_selected_poll_background_color' => '#2d88ff',
                'feed_selected_poll_text_color' => '#fff',
                'feed_poll_sub_text_color' => '#94a3b8',
                'feed_content_share_background_color' => '#323f4f',
                'feed_slider_dot_background_color' => '#d9d9d9',
                'feed_slider_dot_active_background_color' => '#2d88ff',
                'feed_action_border_color' => '#ffffff26',
                'feed_like_text_color' => '#fff',
                'feed_action_item_color' => '#fff',
                'feed_like_active_color' => '#eb3349',
                'feed_bookmark_active_color' => '#2d88ff',
                'feed_comment_form_border_color' => '#ffffff26',
                'feed_comment_form_background_color' => '#334155',
                'feed_comment_form_placeholder_color' => '#94a3b8',
                'feed_comment_form_text_color' => '#fff',
                'feed_comment_form_icon_color' => '#fff',
                'feed_comment_form_button_color' => '#2d88ff',
                'feed_notifications_button_bg_color' => '#2d88ff',
                'feed_notifications_button_text_color' => '#fff'
            ],
            'form' => [
                'button_primary_background_color' => '#2d88ff',
                'button_primary_border_color' => '#2d88ff',
                'button_primary_text_color' => '#fff',
                'button_secondary_background_color' => '#334155',
                'button_secondary_border_color' => '#334155',
                'button_secondary_text_color' => '#fff',
                'button_outlined_background_color' => 'transparent',
                'button_outlined_border_color' => '#2d88ff',
                'button_outlined_text_color' => '#2d88ff',
                'button_secondary_outlined_background_color' => 'transparent',
                'button_secondary_outlined_border_color' => '#ffffff4d',
                'button_secondary_outlined_text_color' => '#fff',
                'button_transparent_background_color' => 'transparent',
                'button_transparent_border_color' => 'transparent',
                'button_transparent_text_color' => '#2d88ff',
                'button_danger_background_color' => '#EB5757',
                'button_danger_border_color' => '#EB5757',
                'button_danger_text_color' => '#fff',
                'button_danger_outlined_background_color' => 'transparent',
                'button_danger_outlined_border_color' => '#EB5757',
                'button_danger_outlined_text_color' => '#EB5757',
                'button_warning_background_color' => '#eea236',
                'button_warning_border_color' => '#eea236',
                'button_warning_text_color' => '#fff',
                'button_success_background_color' => '#00b901',
                'button_success_border_color' => '#00b901',
                'button_success_text_color' => '#fff',
                'button_info_background_color' => '#46b8da',
                'button_info_border_color' => '#46b8da',
                'button_info_text_color' => '#fff',
                'input_background_color' => '#1e293b',
                'input_border_color' => '#ffffff26',
                'input_text_color' => '#fff',
                'input_icon_color' => '#94a3b8',
                'input_placeholder_color' => '#cbd5e1',
                'select_background_color' => '#1e293b',
                'select_border_color' => '#ffffff26',
                'select_text_color' => '#fff',
                'select_text_color_hover' => '#fff',
                'select_background_color_hover' => '#111827',
                'select_text_color_selected' => '#2d88ff',
                'select_background_color_selected' => '#334155',
                'switch_color' => '#334155',
                'switch_active_color' => '#2d88ff',
                'radio_button_color' => '#ffffff26',
                'radio_button_active_color' => '#2d88ff',
                'checkbox_button_color' => '#ffffff26',
                'checkbox_button_active_color' => '#2d88ff',
                'selected_background_color' => '#334155',
                'selected_text_color' => '#fff',
                'selected_icon_color' => '#2d88ff'
            ],
            'chat' => [
                'chat_title_color' => '#fff',
                'chat_user_name_color' => '#fff',
                'chat_options_icon_color' => '#94a3b8',
                'chat_date_color' => '#94a3b8',
                'room_search_background' => '#334155',
                'room_search_border' => '#ffffff26',
                'room_search_placeholder' => '#cbd5e1',
                'room_search_text' => '#fff',
                'room_item_background' => 'transparent',
                'room_item_name' => '#fff',
                'room_item_date' => '#94a3b8',
                'room_item_dropdown_icon' => '#94a3b8',
                'room_item_background_hover' => '#111827',
                'room_item_name_hover' => '#fff',
                'room_item_date_hover' => '#94a3b8',
                'room_item_background_active' => '#1f3452',
                'room_item_name_active' => '#fff',
                'room_item_date_active' => '#94a3b8',
                'room_item_dot_active' => '#2d88ff',
                'message_item_background' => '#1f3452',
                'message_item_border' => '#1f3452',
                'message_item_color' => '#fff',
                'owner_message_item_background' => '#2d88ff',
                'owner_message_item_border' => '#2d88ff',
                'owner_message_item_color' => '#fff',
                'waiting_request_background' => '#475569',
                'waiting_request_border' => '#475569',
                'waiting_request_text' => '#cbd5e1',
                'accept_btn_background' => 'transparent',
                'accept_btn_border' => 'ffffff26',
                'accept_btn_text' => '#27ae60',
                'delete_btn_background' => 'transparent',
                'delete_btn_border' => 'ffffff26',
                'delete_btn_text' => '#eb3349',
                'message_form_background_color' => '#1e293b',
                'message_form_placeholder_color' => '#94a3b8',
                'message_form_text_color' => '#fff',
                'send_message_btn' => '#2d88ff',
                'scroll_bottom_btn_background' => '#334155',
                'scroll_bottom_btn_color' => '#2d88ff',
                'chat_bubble_background_color' => '#2d88ff',
                'chat_bubble_icon_color' => '#fff'
            ],
            'cookies_warning' => [
                'cookies_warning_background_color' => '#1e293b',
                'cookies_warning_text_color' => '#fff',
            ],
            'mention' => [
                'mention_background_color' => '#1e293b',
                'mention_title_color' => '#fff',
                'mention_sub_color' => '#94a3b8',
                'mention_background_active' => '#323f4f'
            ],
            'fetch_link' => [
                'fetch_link_close_icon_color' => '#2d88ff',
                'fetch_link_background_color' => '#2d88ff',
                'fetch_link_text_color' => '#fff',
            ],
            'video_player' => [
                'video_player_progress_color' => '#2d88ff',
                'video_player_volume' => '#2d88ff'
            ],
            'pages' => [
                'pages_switch_color' => '#66c7f4'
            ],
            'vibb' => [
                'vibb_menu_color' => '#fff',
                'vibb_menu_active_color' => '#2d88ff',
                'vibb_menu_active_border_color' => '#2d88ff',
                'vibb_menu_icon_color' => '#fff',
                'vibb_action_background_color' => '#334155',
                'vibb_action_icon_color' => '#fff',
                'vibb_action_text_color' => '#fff',
                'vibb_icon_active_color' => '#eb3349',
                'vibb_icon_bookmark_active_color' => '#2d88ff',
            ],
            'tabs_menu' => [
                'tabs_menu_item_background_color' => '#1e293b',
                'tabs_menu_item_icon_color' => '#fff',
                'tabs_menu_item_text_color' => '#fff',
                'tabs_menu_item_active_background_color' => '#2d88ff',
                'tabs_menu_item_active_icon_color' => '#fff',
                'tabs_menu_item_active_text_color' => '#fff',
            ],
            'invites' => [
                'invites_button_background_color' => '#334155',
                'invites_button_border_color' => '#ffffff26',
                'invites_button_text_color' => '#fff',
                'invites_button_icon_color' => '#2d88ff'
            ],
            'badges' => [
                'ads_badge_background_color' => '#334155',
                'ads_badge_text_color' => '#fff',
                'label_badge_background_color' => '#334155',
                'label_badge_text_color' => '#fff',
                'pinned_badge_background_color' => '#334155',
                'pinned_badge_text_color' => '#fff'
            ]
        ];
    }
}

if (! function_exists('checkAppWeb')) {
    function checkAppWeb()
    {
        $userAgent = request()->userAgent();
        return (strpos($userAgent,'ShaunApp') !== false);
    }
}

if (! function_exists('getUserGuest')) {
    function getUserGuest()
    {
        return new User([
            'user_name' => 'guest',
            'id' => 0,
            'name' => __('Guest'),
            'role_id' => config('shaun_core.role.id.guest')
        ]);
    }
}

if (! function_exists('getInviteLimit')) {
    function getInviteLimit()
    {       
        $count = setting('feature.invite_email_per_day');
        if ($count < 1 || $count > 1000) {
            $count = 10;
        }

        return $count;
    }
}

if (! function_exists('converBlurColor')) {
    function converBlurColor($color)
    {       
        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
        $list = collect([$r, $g, $b]);
        $v = 75;
        $list = $list->map(function ($item, int $key) use ($v) {
            $result = $item - 0.8/255*$v*$item + $v;
            return $result > 255 ? 255 : $result;
        });
        return dechex($list[0]) . dechex($list[1]) . dechex($list[2]);
    }
}

if (! function_exists('checkRecursiveKeyArray')) {
    function checkRecursiveKeyArray($key, $array)
    { 
        $list = explode('.',$key);
		if (count($list) > 1) {
			if (isset($array[$list[0]]) && is_array($array[$list[0]])) {
				$value = $array[$list[0]];
				array_shift($list);
				return checkRecursiveKeyArray(implode('.',$list), $value);
			}
			
			return null;
		} else {
			return isset($array[$key]) ? $array[$key] : null;
		}
    }
}

if (! function_exists('getInviteMax')) {
    function getInviteMax()
    {       
        $count = setting('feature.invite_max');
        if ($count < 1 || $count > 100) {
            $count = 10;
        }

        return $count;
    }
}

if (! function_exists('getShareEmailMax')) {
    function getShareEmailMax()
    {       
        $count = setting('feature.share_email_max');
        if ($count < 1 || $count > 100) {
            $count = 10;
        }

        return $count;
    }
}

if (! function_exists('alreadyUpdate')) {
    function alreadyUpdate($packageName, $version)
    {
        $result = (alreadyInstalled() && setting('site.title'));
        if ($result) {
            $package = Package::where('name', $packageName)->first();
            if (! $package) {
                return true;
            }

            return ($package->version < $version);
        }

        return false;
    }
}

if (! function_exists('updatePackageVersion')) {
    function updatePackageVersion($packageName, $version)
    {
        $package = Package::where('name', $packageName)->first();
        if ($package) {
            $package->update([
                'version' => $version
            ]);
        }
    }
}

if (! function_exists('runSqlFile')) {
    function runSqlFile($sqlFile)
    {
        if (file_exists($sqlFile)) {
            $prefix = env('DB_PREFIX', '');
            $sql = file_get_contents($sqlFile);
            $sql = str_replace('{prefix}', $prefix, $sql);
            DB::unprepared($sql); 
        }
    }
}

if (! function_exists('validateJson')) {
    function validateJson($json)
    {
        if (! $json) {
            return false;
        }
        json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (! function_exists('convertJsonFromString')) {
    function convertJsonFromString($json)
    {
        if (validateJson($json)) {
            return json_decode($json, true);
        }
        return [];
    }
}

if (! function_exists('getPackageName')) {
    function getPackageName($folderName)
    {
        $results = explode('_', $folderName);
        $results = array_map('ucfirst', $results);

        return implode('', $results);
    }
}

if (! function_exists('getQueryFromBuilder')) {
    function getQueryFromBuilder($builder)
    {
        return vsprintf(str_replace(array('?'), array('\'%s\''), $builder->toSql()), $builder->getBindings());
    }
}

if (! function_exists('getCountryData')) {
    function getCountryData()
    {
        return [
            'country_id' => 0,
            'state_id' => 0,
            'city_id' => 0,
            'zip_code' => '',
            'address' => '',
        ];
    }
}

if (! function_exists('cleanCountryData')) {
    function cleanCountryData($data)
    {
        $countryData = getCountryData();
        foreach (array_keys($countryData) as $key) {
            if (empty($data[$key])) {
                if ($key == 'zip_code' || $key == 'address') {
                    $data[$key] = '';
                } else {
                    $data[$key] = 0;
                }
            }
        }

        if (! $data['country_id']) {
            $data['zip_code'] = '';
        }
        
        return $data;
    }
}

if (! function_exists('subDate')) {
    function subDate($start, $end)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        return $startDate->diffInDays($endDate);
    }
}

if (! function_exists('getDateFromTimeZone')) {
    function getDateFromTimeZone($date, $timezone)
    {
        return Carbon::createFromFormat('Y-m-d', $date, $timezone)->setTimezone('UTC')->format('Y-m-d');
    }
}

if (! function_exists('convertDateToInteger')) {
    function convertDateToInteger($date)
    {
        return date('Ymd', strtotime($date));
    }
}

if (! function_exists('getUniqueFromRequest')) {
    function getUniqueFromRequest($request)
    {
        $viewer = $request->user();
        if ($viewer) {
            return md5($viewer->currentAccessToken()->token);
        }

        return md5($request->ip());
    }
}

if (! function_exists('checkFirebaseEnable')) {
    function checkFirebaseEnable()
    {
        return file_exists(base_path('firebase.json'));
    }
}

if (! function_exists('checkAppApi')) {
    function checkAppApi()
    {
        $userAgent = request()->userAgent();
        return (strpos($userAgent,'ShaunApp') !== false);
    }
}

if (! function_exists('getMaxTextSql')) {
    function getMaxTextSql($value)
    {
        if (! $value || $value > config('shaun_core.core.sql_text_max_length')) {
            return config('shaun_core.core.sql_text_max_length');
        }

        return $value;
    }
}

if (! function_exists('getPermissionMessagesForApi')) {
    function getPermissionMessagesForApi()
    {
        $permissions = Permission::getPermissionsForUser();
        $results = [];
        foreach ($permissions as $permission) {
            $message = '';
            if ($permission->haveMessagePermission()) {
                $message = $permission->getTranslatedAttributeValue('message_error');
            }
            $results[$permission->key] = $message;
        }
        return $results;
    }
}

if (! function_exists('removeSpaceString')) {
    function removeSpaceString($input)
    {
        return preg_replace('/\s+/', '', $input);
    }
}

if (! function_exists('getVideoExtensions')) {
    function getVideoExtensions()
    {
        $setting = explode(',',removeSpaceString(setting('feature.video_support_files')));
        $default = explode(',',config('shaun_core.validation.video'));

        $result = array_intersect($setting, $default);
        if ($result) {
            return implode(',', $result);
        } else {
            return config('shaun_core.validation.video');
        }
    }
}

if (! function_exists('validatePhoneNumber')) {
    function validatePhoneNumber($value)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            if (! $phoneUtil->isValidNumber($phoneUtil->parse($value))) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}

if (! function_exists('clearDataForContentTranslate')) {
    function clearDataForContentTranslate($text)
    {
        $urls = getUrlsFromContent($text);
        foreach ($urls as $url) {
            $text = str_replace($url, '', $text);
        }

        $mentions = getMentionsFromContent($text);
        foreach ($mentions as $mention) {
            $text = str_replace($mention, '', $text);
        }

        $hashtags = getHashtagsFromContent($text);
        foreach ($hashtags as $hashtag) {
            $text = str_replace($hashtag, '', $text);
        }
        return $text;
    }
}

if (! function_exists('gePWAJson')) {
    function getPWAJson()
    {
        $value = setting('pwa.json');
        if ($value) {
            $keys = getListKeyPWAJson();
            foreach ($keys as $key) {
                $value = str_replace($key, '"'.$key.'"', $value);
            }
        }

        return $value;
    }
}

if (! function_exists('getListKeyPWAJson')) {
    function getListKeyPWAJson()
    {
        return [
            'apiKey',
            'authDomain',
            'projectId',
            'storageBucket',
            'messagingSenderId',
            'appId',
            'measurementId'
        ];
    }
}

if (! function_exists('checkEnablePWA')) {
    function checkEnablePWA()
    {
        if (! setting('pwa.enable')) {
            return false;
        }
        $json = getPWAJson();

        if (! validateJson($json)) {
            return false;
        }
        
        $json = json_decode($json, true);
        $keys = getListKeyPWAJson();
        
        foreach ($keys as $key) {
            if (empty($json[$key])) {
                if ($key != 'measurementId') {
                    return false;
                }
            }
        }
        return true;
    }
}

if (! function_exists('getConfgPWA')) {
    function getConfgPWA()
    {
        $enable = checkEnablePWA();
        $result = [
            'enable' => $enable,
            'key_pair' => setting('pwa.key_pair')
        ];
        $json = json_decode(getPWAJson(), true);
        $keys = getListKeyPWAJson();
        foreach ($keys as $key) {
            $result[$key] = $enable ? ($json[$key] ?? ''): '';
        }
        
        return $result;
    }
}