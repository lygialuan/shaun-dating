<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;

class GroupPostPending extends Model
{
    use HasUser, IsSubject, HasCacheQueryFields, HasCachePagination;
    
    protected $cacheQueryFields = [
        'id'
    ];

    public function getListCachePagination()
    {
        return [
            'group_post_pending_user_'.$this->group_id.'_'.$this->user_id,
        ];
    }

    protected $fillable = [
        'user_id',
        'group_id',
        'post_id',
        'post_type',
        'notify_sent',
        'post_content'
    ];

    public function getGroup()
    {
        return Group::findByField('id', $this->group_id);
    }

    public function getPost()
    {
        return Post::findByField('id', $this->post_id);
    }

    public static function getCountByUser($userId, $groupId)
    {
        if (! $userId) {
            return 0;
        }
        return Cache::remember(self::getCacheCountByUserKey($userId, $groupId), config('shaun_core.cache.time.model_query'), function () use ($userId, $groupId) {
            return self::where('user_id', $userId)->where('group_id', $groupId)->count();
        });
    }

    public static function getCountByGroup($groupId)
    {
        return self::where('group_id', $groupId)->count();
    }

    public static function getCacheCountByGroupKey($groupId)
    {
        return 'group_post_pending_count_by_group_'.$groupId;
    }

    public static function getCacheCountByUserKey($userId, $groupId)
    {
        return 'group_post_pending_count_by_user_'.$userId.'_'.$groupId;
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheCountByGroupKey($this->group_id));
        Cache::forget(self::getCacheCountByUserKey($this->user_id, $this->group_id));
    }
    
    public static function booted()
    {
        parent::booted();

        static::creating(function ($postPending) {
            $postPending->clearCache();

            $group = $postPending->getGroup();
            if ($group) {
                $count = self::getCountByGroup($postPending->group_id);
                $group->update([
                    'post_pending_count' => $count + 1
                ]);
            }
        });

        static::deleting(function ($postPending) {
            $postPending->clearCache();

            $group = $postPending->getGroup();
            if ($group) {
                $count = self::getCountByGroup($postPending->group_id);
                $group->update([
                    'post_pending_count' => $count - 1
                ]);
            }
        });

    }
}
