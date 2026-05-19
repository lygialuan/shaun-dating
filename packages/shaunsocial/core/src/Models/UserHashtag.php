<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Traits\HasSubject;

class UserHashtag extends Model
{
    use Prunable, HasSubject;

    protected $fillable = [
        'user_id',
        'hashtags'
    ];

    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(config('shaun_core.core.user_hastag_delete_day')))->limit(setting('feature.item_per_page'))->distinct('user_id');
    }
    
    static public function getHashtagByUser($userId, $currentId = null)
    {
        $builder = self::where('user_id', $userId)->select(DB::raw('GROUP_CONCAT(hashtags) as hashtags'));
        if ($currentId) {
            $builder->where('id', '!=', $currentId);
        }
        return $builder->first()->hashtags;
    }

    public static function getBySubject($subjectType, $subjectId)
    {
        return Cache::remember(self::getKeyCache($subjectType, $subjectId), config('shaun_core.cache.time.model_query'), function () use ($subjectType, $subjectId) {
            return self::where('subject_type', $subjectType)->where('subject_id', $subjectId)->first();
        });
    }

    public static function getKeyCache($subjectType, $subjectId)
    {
        return 'user_hashtag_'.$subjectType.'_'.$subjectId;
    }

    public function clearAllCache()
    {
        Cache::forget(self::getKeyCache($this->subject_type, $this->subject_id));
    }
    
    public static function booted()
    {
        parent::booted();

        static::creating(function ($userHastag) {
            $hashtags = self::getHashtagByUser($userHastag->user_id);
            $hashtags = $hashtags ? $hashtags.','.$userHastag->hashtags : $userHastag->hashtags;
            $user = User::findByField('id', $userHastag->user_id);
            $user->update([
                'hashtags' => getHashtagFromUserHashtag($hashtags),
            ]);

            $userHastag->clearAllCache();
        });
        
        static::updated(function ($userHastag) {
            $hashtags = self::getHashtagByUser($userHastag->user_id, $userHastag->id);
            $hashtags = $hashtags ? $hashtags.','.$userHastag->hashtags : $userHastag->hashtags;
            $user = User::findByField('id', $userHastag->user_id);
            if ($user) {
                $user->update([
                    'hashtags' => getHashtagFromUserHashtag($hashtags),
                ]);
                
                $userHastag->clearAllCache();
            }
        });

        static::deleting(function ($userHastag) {
            $hashtags = self::getHashtagByUser($userHastag->user_id, $userHastag->id);
            $user = User::findByField('id', $userHastag->user_id);
            if ($user) {                
                $user->update([
                    'hashtags' => getHashtagFromUserHashtag($hashtags),
                ]);
                $userHastag->clearAllCache();
            }
        });
    }
}
