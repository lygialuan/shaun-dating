<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MailUnsubscribe extends Model
{
    protected $fillable = [
        'email',
    ];

    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($mailUnsubscribe) {
            Cache::forget(self::getCacheKey($mailUnsubscribe->email));
        });

        static::deleted(function ($mailUnsubscribe) {
            Cache::forget(self::getCacheKey($mailUnsubscribe->email));
        });
    }

    public static function getByEmail($email)
    {
        return Cache::remember(self::getCacheKey($email), config('shaun_core.cache.time.mail_unsubscrite'), function () use ($email) {
            $mailUnsubscribe = self::where('email', $email)->first();
            
            return $mailUnsubscribe;
        });
    }

    public static function getCacheKey($email)
    {
        return 'mail_unsubscribe_'.md5($email);
    }
}
