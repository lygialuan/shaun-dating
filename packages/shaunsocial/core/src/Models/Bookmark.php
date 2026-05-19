<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasUser;

class Bookmark extends Model
{
    use HasCachePagination, HasUser, HasSubject;

    protected $fillable = [
        'user_id'
    ];

    public function getListCachePagination()
    {
        return [
            'bookmark_'.$this->user_id,
        ];
    }

    public static function getBookmark($userId, $subjectType, $subjectId)
    {
        if (! $userId) {
            return false;
        }

        return Cache::remember(self::getKeyCache($userId, $subjectType, $subjectId), config('shaun_core.cache.time.model_query'), function () use ($userId, $subjectType, $subjectId) {
            $bookmark = self::where('user_id', $userId)->where('subject_type', $subjectType)->where('subject_id', $subjectId)->first();

            return is_null($bookmark) ? false : $bookmark;
        });
    }

    public static function getKeyCache($userId, $subjectType, $subjectId)
    {
        return 'bookmark_'.$userId.'_'.$subjectType.'_'.$subjectId;
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($bookmark) {
            Cache::forget(self::getKeyCache($bookmark->user_id, $bookmark->subject_type, $bookmark->subject_id));
        });

        static::deleted(function ($bookmark) {
            Cache::forget(self::getKeyCache($bookmark->user_id, $bookmark->subject_type, $bookmark->subject_id));
        });
    }
}
