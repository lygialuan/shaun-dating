<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasUser;

class Like extends Model
{
    use HasCachePagination, HasUser, HasSubject;

    protected $fillable = [
        'user_id'
    ];

    public function getListCachePagination()
    {
        return [
            'like_'.$this->subject_type.'_'.$this->subject_id,
        ];
    }

    public static function getLike($userId, $subjectType, $subjectId)
    {
        if (! $userId) {
            return false;
        }

        return Cache::remember(self::getKeyCache($userId, $subjectType, $subjectId), config('shaun_core.cache.time.model_query'), function () use ($userId, $subjectType, $subjectId) {
            $like = self::where('user_id', $userId)->where('subject_type', $subjectType)->where('subject_id', $subjectId)->first();

            return is_null($like) ? false : $like;
        });
    }

    public static function getKeyCache($userId, $subjectType, $subjectId)
    {
        return 'like_'.$userId.'_'.$subjectType.'_'.$subjectId;
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($like) {
            Cache::forget(self::getKeyCache($like->user_id, $like->subject_type, $like->subject_id));

            //increase
            $subject = $like->getSubject();
            $count = $subject->getLikeCount();
            $subject->update([
                'like_count' => $count + 1
            ]);

            //add statistic for page
            if (method_exists($subject, 'getUser') && in_array($like->subject_type, ['posts'])) {
                $user = $like->getUser();
                $owner = $subject->getUser();
                if ($user && $owner && $user->id != $owner->id) {
                    $owner->addPageStatistic('post_like', $user, $subject);
                }
            }

            //add statistic for source
            if (method_exists($subject, 'supportSource') && $subject->supportSource()) {
                $user = $like->getUser();
                $subject->addStatisticWithSource('like', $user, $like);
            }
        });

        static::deleting(function ($like) {
            Cache::forget(self::getKeyCache($like->user_id, $like->subject_type, $like->subject_id));

            //decrease
            $subject = $like->getSubject();
            $count = $subject->getLikeCount();
            $subject->update([
                'like_count' => $count - 1
            ]);
        });
    }
}
