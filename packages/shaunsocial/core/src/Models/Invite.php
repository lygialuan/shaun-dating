<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Enum\InviteType;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class Invite extends Model
{
    use HasCacheQueryFields;

    protected $fillable = [
        'user_id',
        'new_user_id',
        'email',
        'type'
    ];

     protected $casts = [
        'type' => InviteType::class,
    ];

    protected $cacheQueryFields = [
        'new_user_id'
    ];

    public function clearInfoCache()
    {
        Cache::forget('invite_info_'.$this->user_id);
        Cache::forget(self::getKeyCache($this->user_id, $this->email));
    }

    public static function getKeyCache($userId, $email)
    {
        return 'invite_user_email_'.$userId.'_'.$email;
    }

    public static function getInviteByUserAndEmail($userId, $email)
    {
        return Cache::remember(self::getKeyCache($userId, $email), config('shaun_core.cache.time.model_query'), function () use ($userId, $email) {
            return self::where('user_id', $userId)->where('email', $email)->first();
        });
    }

    public function getStatus()
    {
        if ($this->new_user_id) {
            return 'joined';
        }
        return 'sent';
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($invite) {
            $invite->clearInfoCache();
        });

        static::updated(function ($invite) {
            $invite->clearInfoCache();
        });

        static::deleted(function ($invite) {
            $invite->clearInfoCache();
        });
    }
}
