<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;

class GroupMemberRequest extends Model
{
    use HasUser, HasCacheQueryFields, IsSubject;

    protected $fillable = [ // define saved data when use create($data) and update($data)
        'group_id',
        'user_id',
    ];

    protected $cacheQueryFields = [
        'id'
    ];

    public function getGroup()
    {
        return Group::findByField('id', $this->group_id);
    }

    public static function getCountByGroup($groupId)
    {
        return self::where('group_id', $groupId)->count();
    }

    public static function getRequest($userId, $groupId)
    {
        if (! $userId) {
            return false;
        }
        
        return Cache::remember(self::getCacheGroupMemberRequest($userId, $groupId), config('shaun_core.cache.time.model_query'), function () use ($userId, $groupId) {
            $request = self::where('user_id', $userId)->where('group_id', $groupId)->first();

            return is_null($request) ? false : $request;
        });
    }

    public static function getCacheGroupMemberRequest($userId, $groupId)
    {
        $key = $groupId;
        $group = Group::findByField('id', $groupId);
        if ($group) {
            $key = $groupId.'_'.$group->cache_key;
        }
        return 'group_member_request_'.$userId.'_'.$key;
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheGroupMemberRequest($this->user_id, $this->group_id));
    }

    public static function booted()
    {
        parent::booted();

        static::saved(function ($request) {
            $request->clearCache();
        });

        static::deleted(function ($request) {
            $request->clearCache();
        });

        static::creating(function ($request) {
            $group = $request->getGroup();
            if ($group) {
                $count = self::getCountByGroup($request->group_id);
                $group->update([
                    'member_request_count' => $count + 1
                ]);
            }
        });

        static::deleting(function ($request) {
            $group = $request->getGroup();
            if ($group) {
                $count = self::getCountByGroup($request->group_id);
                $group->update([
                    'member_request_count' => $count - 1
                ]);
            }
        });
    }
}
