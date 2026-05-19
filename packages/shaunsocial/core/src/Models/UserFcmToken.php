<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class UserFcmToken extends Model
{
    use HasCacheQueryFields;
    
    protected $cacheQueryFields = [
        'user_id',
        'hash'
    ];

    protected $fillable = [
        'user_id',
        'token',
        'type',
        'hash'
    ];

    public static function getTypes()
    {
        return [
            'android',
            'ios',
            'web'
        ];
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($token) {            
            $token->hash = md5($token->token);
        });
    }
}
