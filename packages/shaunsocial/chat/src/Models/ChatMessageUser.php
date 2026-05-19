<?php


namespace Packages\ShaunSocial\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;

class ChatMessageUser extends Model
{
    use HasCachePagination;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'user_id',
        'room_id',
        'message_id',
        'is_read',
        'is_delete'
    ];

    protected $casts = [
        'is_delete' => 'boolean',
        'is_read' => 'boolean',
    ];

    public function getListFieldPagination()
    {
        return [
            'is_read',
            'is_delete'
        ];
    }

    public function getListCachePagination()
    {
        return [
            'chat_message_'.$this->room_id.'_'.$this->user_id
        ];
    }

    public static function getCacheUserMessageKey($messageId, $userId)
    {
        return 'chat_message_user_by_user_message_'.$messageId.'_'.$userId;
    }

    public static function getByUserMessage($messageId, $userId)
    {
        return Cache::remember(self::getCacheUserMessageKey($messageId, $userId), config('shaun_core.cache.time.model_query'), function () use ($messageId, $userId) {
            return self::where('message_id', $messageId)->where('user_id', $userId)->first();
        });
    }

    public static function getCacheLastMessageIdSeenKey($roomId, $userId)
    {
        return 'chat_message_user_by_last_message_id_seen_'.$roomId.'_'.$userId;
    }

    public static function getLastMessageIdSeen($roomId, $userId)
    {
        return Cache::remember(self::getCacheLastMessageIdSeenKey($roomId, $userId), config('shaun_core.cache.time.model_query'), function () use ($roomId, $userId) {
            return self::where('room_id', $roomId)->where('user_id', $userId)->where('is_read', true)->orderBy('id', 'DESC')->first();
        });
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheLastMessageIdSeenKey($this->room_id, $this->user_id));
        Cache::forget(self::getCacheUserMessageKey($this->message_id, $this->user_id));
    }

    public function updateRoomCount()
    {
        $messageRoomCount = ChatMessageUser::where('user_id', $this->user_id)->where('room_id', $this->room_id)->where('is_read', false)->count();
        $room = ChatRoom::findByField('id', $this->room_id);
        $roomMember = $room->getMember($this->user_id);
        $checkBefore = $roomMember->message_count;
        $roomMember->update([
            'message_count' => $messageRoomCount + 1 
        ]);

        $user = $roomMember->getUser();
        if ($user->id > 0 && !$checkBefore && $roomMember->enable_notify && $roomMember->status == 'accepted') {
            $messageCount = $roomMember->getUserChatCount();
        
            $user->update([
                'chat_count' => $messageCount + 1 
            ]);
        }
    }

    public static function booted()
    {
        parent::booted();

        static::updated(function ($messageUser) {
            $messageUser->clearCache();
        });

        static::creating(function ($messageUser) {
            $messageUser->clearCache();
            if (! $messageUser->is_read) {
                $messageUser->updateRoomCount();
            }            
        });
    }
}
