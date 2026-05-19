<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserListMember extends Model
{
    use HasCacheQueryFields, HasUser;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'user_id',
        'user_list_id'
    ];

    public function getUserList()
    {
        return UserList::findByField('id', $this->user_list_id);
    }

    static function checkExist($id, $userId)
    {
        return Cache::remember(self::getCacheKey($id, $userId), config('shaun_core.cache.time.check_exist'), function () use ($id, $userId) {
            $member = self::where('user_id', $userId)->where('user_list_id', $id)->first();

            return is_null($member) ? false : $member;
        });
    }

    public static function getCacheKey($id, $userId)
    {
        return 'user_list_memeber_'.$id.'_'.$userId;
    }

    public function clearCache()
    {
        Cache::forget($this->getCacheKey($this->user_list_id, $this->user_id));
    }

    public static function booted()
    {
        parent::booted();

        static::deleting(function ($member) {
            $member->clearCache();
            $list = $member->getUserList();
            if ($list) {
                $count = $list->getMemberCount();
                $list->update([
                    'member_count' => $count - 1
                ]);
            }
        });

        static::creating(function ($member) {
            $member->clearCache();
            $list = $member->getUserList();
            if ($list) {
                $count = $list->getMemberCount();
                $list->update([
                    'member_count' => $count + 1
                ]);
            }
        });
    }
}
