<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\UserPage\Enum\UserPageAdminRole;

class UserPageAdmin extends Model
{
    use HasUser, HasCacheQueryFields, HasCachePagination;
    
    protected $cacheQueryFields = [
        'user_page_id',
        'user_id'
    ];

    protected $fillable = [
        'user_id',
        'user_page_id',
        'role',
        'notify_setting'
    ];

    protected $notifyDefault = [
        'page_notify' => true,
        'message_notify' => true,
    ];

    public function getListCachePagination()
    {
        return [
            'user_page_my_'.$this->user_id,
            'user_page_admin_'.$this->user_page_id,
        ];
    }

    protected $casts = [
        'role' => UserPageAdminRole::class
    ];

    public static function getCachePageAdminKey($userId, $userPageId)
    {
        return 'user_page_admin_'.$userId.'_'.$userPageId;
    }

    public static function getCachePageOwnerKey($userPageId)
    {
        return 'user_page_owner_'.$userPageId;
    }

    public function clearCache()
    {
        Cache::forget(self::getCachePageAdminKey($this->user_id, $this->user_page_id));
        Cache::forget(self::getCachePageOwnerKey($this->user_page_id));
    }

    public static function getAdmin($userId, $userPageId)
    {
        return Cache::remember(self::getCachePageAdminKey($userId, $userPageId), config('shaun_core.cache.time.model_query'), function () use ($userId, $userPageId) {
            $admin = self::where('user_id', $userId)->where('user_page_id', $userPageId)->first();

            return is_null($admin) ? false : $admin;
        });
    }

    public static function getOwner($userPageId)
    {
        return Cache::remember(self::getCachePageOwnerKey($userPageId), config('shaun_core.cache.time.model_query'), function () use ($userPageId) {
            $owner = self::where('role', UserPageAdminRole::OWNER)->where('user_page_id', $userPageId)->first();

            return is_null($owner) ? false : $owner;
        });
    }

    public function getPage()
    {
        return User::findByField('id', $this->user_page_id);
    }

    public function isPageOwner()
    {
        return $this->role == UserPageAdminRole::OWNER;
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
        return array_merge($this->notifyDefault, $result);
    }

    public function checkNotifySetting($name)
    {
        $notifySetting = $this->getNotifySetting();

        return isset($notifySetting[$name]) ? $notifySetting[$name] : false;
    }

    public function getRoleName()
    {
        switch ($this->role) {
            case UserPageAdminRole::ADMIN:
                return __('Moderator');
                break;
            case UserPageAdminRole::OWNER:
                return __('Owner');
                break;
        }
    }
    
    public static function booted()
    {
        parent::booted();

        static::saved(function ($admin) {
            $admin->clearCache();
        });

        static::deleted(function ($admin) {
            $admin->clearCache();

            //delete all token
            $tokens = UserPageToken::where('user_id', $admin->user_id)->where('user_page_id', $admin->user_page_id)->get();
            $tokens->each(function($token){
                $token->delete();
            });
        });
    }
}
