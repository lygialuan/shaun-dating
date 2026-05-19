<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Group\Enum\GroupMemberRole;

class GroupMember extends Model
{
    use HasUser, HasCacheQueryFields, HasCachePagination;
    
    protected $cacheQueryFields = [
        'id',
        'group_id',
        'user_id'
    ];

    protected $fillable = [
        'user_id',
        'group_id',
        'role',
        'notify_setting',
        'last_active'
    ];

    protected $notifyDefault = [
        'request_join_notify' => true,
        'pending_post_notify' => true,
        'pin_post' => true,
        'post_new' => true
    ];

    public function getListCachePagination()
    {
        return [
            'group_my_'.$this->user_id,
            'group_admin_'.$this->group_id,
        ];
    }

    public function getListFieldPagination()
    {
        return [
            'role',
            'user_id'
        ];
    }

    protected $casts = [
        'role' => GroupMemberRole::class,
        'last_active' => 'datetime'
    ];

    public static function getCacheGroupMemberKey($userId, $groupId)
    {
        $key = $groupId;
        $group = Group::findByField('id', $groupId);
        if ($group) {
            $key = $groupId.'_'.$group->cache_key;
        }

        return 'group_member_'.$userId.'_'.$key;
    }

    public static function getCacheGroupOverviewKey($groupId)
    {
        return 'group_member_overview_'.$groupId;
    }

    public static function getCacheGroupOwnerKey($groupId)
    {
        return 'group_member_owner_'.$groupId;
    }

    public function clearCache()
    {
        if ($this->getOriginal('user_id')) {
            Cache::forget(self::getCacheGroupMemberKey($this->getOriginal('user_id'), $this->group_id));
        }

        Cache::forget(self::getCacheGroupMemberKey($this->user_id, $this->group_id));
        Cache::forget(self::getCacheGroupOwnerKey($this->group_id));
        Cache::forget(self::getCacheGroupOverviewKey($this->group_id));
        Cache::forget(self::getCacheGroupIdsByUserKey($this->group_id));
        Cache::forget(self::getCacheAdminListKey($this->group_id));
        foreach ([1,2] as $value) {
            Cache::forget('group_explore_'.$this->user_id.'_'.$value);
        }
    }

    public static function getAdmin($userId, $groupId)
    {
        $member = self::getMember($userId, $groupId);
        if ($member && $member->isAdmin()) {
            return $member;
        }
        return false;
    }

    public function isAdmin()
    {
        return in_array($this->role, [GroupMemberRole::ADMIN, GroupMemberRole::OWNER]);
    }

    public function isOwner()
    {
        return in_array($this->role, [GroupMemberRole::OWNER]);
    }

    public static function getMember($userId, $groupId)
    {
        if (! $userId) {
            return false;
        }
        
        return Cache::remember(self::getCacheGroupMemberKey($userId, $groupId), config('shaun_core.cache.time.model_query'), function () use ($userId, $groupId) {
            $member = self::where('user_id', $userId)->where('group_id', $groupId)->first();

            return is_null($member) ? false : $member;
        });
    }

    public static function checkOwner($userId, $groupId)
    {
        $member = self::getMember($userId, $groupId);
        if ($member && $member->isOwner()) {
            return $member;
        }
        return false;
    }

    public static function getOwner($groupId)
    {
        return Cache::remember(self::getCacheGroupOwnerKey($groupId), config('shaun_core.cache.time.model_query'), function () use ($groupId) {
            $owner = self::where('role', GroupMemberRole::OWNER)->where('group_id', $groupId)->first();

            return is_null($owner) ? false : $owner;
        });
    }

    public static function getAdminList($groupId)
    {
        return Cache::remember(self::getCacheAdminListKey($groupId), config('shaun_core.cache.time.model_query'), function () use ($groupId) {
            return self::whereIn('role', [GroupMemberRole::OWNER, GroupMemberRole::ADMIN ])->where('group_id', $groupId)->get();
        });
    }

    public static function getOverviewMembers($groupId)
    {
        return Cache::remember(self::getCacheGroupOverviewKey($groupId), config('shaun_core.cache.time.model_query'), function () use ($groupId) {
            return self::where('group_id', $groupId)->orderBy('id', 'desc')->limit(config('shaun_group.limit_overview_member'))->get();
        });
    }

    public static function getCacheAdminListKey($groupId)
    {
        return 'group_member_admin_list_'.$groupId;
    }

    public function getGroup()
    {
        return Group::findByField('id', $this->group_id);
    }

    public function isGroupOwner()
    {
        return $this->role == GroupMemberRole::OWNER;
    }

    public function getNotifySetting()
    {
        $result = [];
        if ($this->notify_setting) {
            $result = json_decode($this->notify_setting, true);
            if (! $result) {
                $result = [];
            }
        }
        
        $result = array_merge($this->notifyDefault, $result);
        if (! $this->isAdmin()) {
            return [
                'pin_post' => $result['pin_post'],
                'post_new' => $result['post_new']
            ];
        }

        return $result;
    }

    public function checkNotifySetting($name)
    {
        $notifySetting = $this->getNotifySetting();

        return isset($notifySetting[$name]) ? $notifySetting[$name] : false;
    }

    public function getRoleName()
    {
        switch ($this->role) {
            case GroupMemberRole::ADMIN:
                return __('Moderator');
                break;
            case GroupMemberRole::OWNER:
                return __('Owner');
                break;
        }
    }

    public static function getCountByUser($userId)
    {
        return self::where('user_id', $userId)->count();
    }

    public static function getCountByGroup($groupId)
    {
        return self::where('group_id', $groupId)->count();
    }

    public static function getCountAdminByGroup($groupId)
    {
        return self::where('group_id', $groupId)->where('role', GroupMemberRole::ADMIN)->count();
    }

    public static function getGroupIdsByUser($userId)
    {
        return Cache::remember(self::getCacheGroupIdsByUserKey($userId), config('shaun_core.cache.time.model_query'), function () use ($userId) {
            return self::where('user_id', $userId)->get()->pluck('group_id')->all();
        });
    }
    
    public static function getCacheGroupIdsByUserKey($userId)
    {
        return 'group_member_ids_by_user_'.$userId;
    }
    
    public static function booted()
    {
        parent::booted();

        static::created(function ($member) {
            $member->clearCache();
        });

        static::saved(function ($member) {
            $member->clearCache();
        });

        static::deleted(function ($member) {
            $member->clearCache();
        });

        static::creating(function ($member) {
            $user = $member->getUser();
            if ($user) {
                $count = self::getCountByUser($member->user_id);
                $user->update([
                    'group_count' => $count + 1
                ]);
            }

            $group = $member->getGroup();
            if ($group) {
                $count = self::getCountByGroup($member->group_id);
                $countAdmin = self::getCountAdminByGroup($member->group_id);
                if ($member->role == GroupMemberRole::ADMIN) {
                    $countAdmin++;
                }
                $group->update([
                    'member_count' => $count + 1,
                    'admin_count' => $countAdmin
                ]);
            }

            $member->last_active = now();

            GroupStatistic::add($member->group_id, 'member', $member->getUser(), null , false);
        });

        static::deleting(function ($member) {
            $user = $member->getUser();
            if ($user) {
                $count = self::getCountByUser($member->user_id);
                $user->update([
                    'group_count' => $count - 1
                ]);
            }

            $group = $member->getGroup();
            if ($group) {
                $count = self::getCountByGroup($member->group_id);
                $countAdmin = self::getCountAdminByGroup($member->group_id);
                if ($member->role == GroupMemberRole::ADMIN) {
                    $countAdmin--;
                }
                $group->update([
                    'member_count' => $count - 1,
                    'admin_count' => $countAdmin
                ]);
            }

            GroupStatistic::remove($member->group_id, 'member', $member->user_id);
        });

    }
}
