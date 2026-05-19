<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TranslateProvider extends Model
{
    protected $fillable = [
        'name',
        'class',
        'is_default',
        'type',
        'config'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public $timestamps = false;

    static function getDefault()
    {
        return Cache::rememberForever('translate_provider_default', function () {
            return self::where('is_default', true)->first();
        });
    }

    public function getConfig()
    {
        return $this->config ? json_decode($this->config, true) : [];
    }

    public function getClass($config = null)
    {
        $config = $config ?? $this->getConfig();
        return app($this->class)->setConfig($config);
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($provider) {
            Cache::forget('translate_provider_default');
        });

        static::saved(function ($provider) {
            Cache::forget('translate_provider_default');
        });
    }

    public function translate($text, $language)
    {
        $class = $this->getClass();
        $hash = md5($text.$language);
        $history = TranslateContentHistory::findByField('hash', $hash);
        if ($history) {
            return ['status' => true, 'content' => $history->result];
        }

        $urls = getUrlsFromContent($text);
        $uuidUrls = [];
        foreach ($urls as $url) {
            $uuid = Str::uuid();
            $text = str_replace($url, '[1_'.$uuid.']', $text);
            $uuidUrls[$url] = $uuid;
        }

        $mentions = getMentionsFromContent($text);
        $uuidMentions = [];
        foreach ($mentions as $mention) {
            $uuid = Str::uuid();
            $text = str_replace($mention, '[2_'.$uuid.']', $text);
            $uuidMentions[$mention] = $uuid;
        }

        $hashtags = getHashtagsFromContent($text);
        $uuidHashtags = [];
        foreach ($hashtags as $hashtag) {
            $uuid = Str::uuid();
            $text = str_replace($hashtag, '[3_'.$uuid.']', $text);
            $uuidHashtags[$hashtag] = $uuid;
        }

        $result = $class->translate($text, $language);
        if ($result['status']) {
            $text = $result['content'];
            foreach ($uuidUrls as $url => $uuid) {
                $text = str_replace('[1_'.$uuid.']', $url, $text);
            }

            foreach ($uuidMentions as $mention => $uuid) {
                $text = str_replace('[2_'.$uuid.']', $mention, $text);
            }

            foreach ($uuidHashtags as $hashtag => $uuid) {
                $text = str_replace('[3_'.$uuid.']', $hashtag, $text);
            }

            $result['content'] = $text;

            TranslateContentHistory::create([
                'hash' => $hash,
                'result' => $text
            ]);
        } else {
            $result['message'] = __('Translation is unavailable at the moment.');
        }

        return $result;
    }
}
