<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SmsProvider extends Model
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
        return Cache::rememberForever('sms_provider_default', function () {
            return self::where('is_default', true)->first();
        });
    }

    public function getConfig()
    {
        return $this->config ? json_decode($this->config, true) : [];
    }

    public function getClass()
    {
        return app($this->class)->setConfig($this->getConfig());
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($provider) {
            Cache::forget('sms_provider_default');
        });

        static::saved(function ($provider) {
            Cache::forget('sms_provider_default');
        });
    }

    public function sendSms($text, $to)
    {
        $class = $this->getClass();

        return $class->sendSms($text, $to);
    }
}
