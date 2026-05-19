<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasSubject;

class UserNotificationStop extends Model
{
    use HasSubject;

    protected $fillable = [
        'user_id'
    ];

    public static function getNotificationStop($userId, $subjectType, $subjectId)
    {
        if (! $userId) {
            return false;
        }

        return Cache::remember(self::getKeyCache($userId, $subjectType, $subjectId), config('shaun_core.cache.time.model_query'), function () use ($userId, $subjectType, $subjectId) {
            $stop = self::where('user_id', $userId)->where('subject_type', $subjectType)->where('subject_id', $subjectId)->first();

            return is_null($stop) ? false : $stop;
        });
    }

    public static function getKeyCache($userId, $subjectType, $subjectId)
    {
        return 'notification_stop_'.$userId.'_'.$subjectType.'_'.$subjectId;
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($stop) {
            Cache::forget(self::getKeyCache($stop->user_id, $stop->subject_type, $stop->subject_id));
        });

        static::deleted(function ($stop) {
            Cache::forget(self::getKeyCache($stop->user_id, $stop->subject_type, $stop->subject_id));
        });
    }
}
