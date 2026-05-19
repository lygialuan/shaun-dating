<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class GroupBlock extends Model
{
    use HasUser, HasCacheQueryFields;

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

    public static function getKeyCache($userId, $groupId)
    {
        return 'group_block_'.$userId.'_'.$groupId;
    }

    public static function getCountByGroup($groupId)
    {
        return self::where('group_id', $groupId)->count();
    }

    public static function getBlock($userId, $groupId)
    {
        if (! $userId) {
            return false;
        }

        return Cache::remember(self::getKeyCache($userId, $groupId), config('shaun_core.cache.time.model_query'), function () use ($userId, $groupId) {
            $like = self::where('user_id', $userId)->where('group_id', $groupId)->first();

            return is_null($like) ? false : $like;
        });
    }

    public function clearCache()
    {
        Cache::forget(self::getKeyCache($this->user_id, $this->group_id));
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($block) {
            $block->clearCache();
        });

        static::deleted(function ($block) {
            $block->clearCache();
        });

        static::creating(function ($block) {
            $group = $block->getGroup();
            if ($group) {
                $count = self::getCountByGroup($block->group_id);
                $group->update([
                    'block_count' => $count + 1
                ]);
            }
        });

        static::deleting(function ($block) {
            $group = $block->getGroup();
            if ($group) {
                $count = self::getCountByGroup($block->group_id);
                $group->update([
                    'block_count' => $count - 1
                ]);
            }
        });
    }
}
