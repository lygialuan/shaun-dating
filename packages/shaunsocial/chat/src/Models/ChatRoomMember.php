<?php


namespace Packages\ShaunSocial\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasUser;

class ChatRoomMember extends Model
{
    use HasCachePagination, HasUser;

    public function getListCachePagination()
    {
        return [
            'chat_'.$this->user_id,
            'chat_request_'.$this->user_id,
        ];
    }

    protected $fillable = [
        'room_id',
        'user_id',
        'status',
        'last_updated_at',
        'enable_notify',
        'is_owner',
        'is_moderator',
        'message_count',
        'user_name'
    ];

    protected $casts = [
        'enable_notify' => 'boolean',
        'is_owner' => 'boolean',
        'is_moderator' => 'boolean',
    ];

    public function getListFieldPagination()
    {
        return [
            'status',
            'last_updated_at'
        ];
    }

    public static function getCacheRoomKey($roomId)
    {
        return 'chat_room_member_'.$roomId;
    }

    public static function getCacheRequestCountKey($userId)
    {
        return 'chat_room_member_request_count_'.$userId;
    }

    public static function getUsers($roomId)
    {
        return Cache::remember(self::getCacheRoomKey($roomId), config('shaun_core.cache.time.model_query'), function () use ($roomId) {
            return self::where('room_id',$roomId)->get();
        });
    }

    public static function getRequestCount($viewerId)
    {
        return Cache::remember(self::getCacheRequestCountKey($viewerId), config('shaun_core.cache.time.model_query'), function () use ($viewerId) {
            return self::where('user_id', $viewerId)->where('status', 'sent')->count();
        });
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheRoomKey($this->room_id));
        Cache::forget(self::getCacheRequestCountKey($this->user_id));
    }

    public function canChangeStatusSent()
    {
        return in_array($this->status, ['created', 'cancelled']);
    }

    public function canView()
    {
        return in_array($this->status, ['sent', 'accepted']);
    }

    public function canPushNotify()
    {
        return in_array($this->status, ['accepted']) && $this->enable_notify;
    }

    public function getUser()
    {
        $user = User::findByField('id', $this->user_id);
        if (!$user) {
            $user = getDeleteUser();
        }

        return $user;
    }

    public function getUserChatCount()
    {
        return self::where('user_id', $this->user_id)->where('id', '!=', $this->id)->where('status', 'accepted')->where('enable_notify', true)->where('message_count','>', 0)->count();
    }

    public static function updateUserName($userId, $userName)
    {
        self::where('user_id', $userId)->update([
            'user_name' => $userName
        ]);
    }

    public function setWebActive($active)
    {
        if ($active) {
            Cache::put('room_member_web_active_'.$this->id, true, config('shaun_chat.cache.time.active'));
        } else {
            Cache::forget('room_member_web_active_'.$this->id);
        }
    }

    public function setAppActive($active)
    {
        if ($active) {
            Cache::put('room_member_app_active_'.$this->id, true, config('shaun_chat.cache.time.active'));
        } else {
            Cache::forget('room_member_app_active_'.$this->id);
        }
    }

    public function isActive()
    {
        return $this->isWebActive() || $this->isAppActive();
    }

    public function isAppActive()
    {
        return Cache::has('room_member_app_active_'.$this->id);
    }

    public function isWebActive()
    {
        return Cache::has('room_member_web_active_'.$this->id);
    }

    public function getRoom()
    {
        return ChatRoom::findByField('id', $this->room_id);
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($member) {
            $member->clearCache();
        });

        static::updated(function ($member) {
            $member->clearCache();
        });

        static::deleted(function ($member) {
            $member->clearCache();
        });
    }
}
