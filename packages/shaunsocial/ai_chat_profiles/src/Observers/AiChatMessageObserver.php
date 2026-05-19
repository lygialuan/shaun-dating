<?php

namespace Packages\ShaunSocial\AiChatProfiles\Observers;

use Packages\ShaunSocial\AiChatProfiles\Models\AiPersonaConfig;
use Packages\ShaunSocial\AiChatProfiles\Services\AiChatJobDispatcher;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatRoomMember;
use Packages\ShaunSocial\Core\Models\User;

class AiChatMessageObserver
{
    public function __construct(private AiChatJobDispatcher $dispatcher)
    {
    }

    public function created(ChatMessage $message): void
    {
        if ($message->type !== 'text' || $message->is_delete) return;

        $senderId = (int) $message->user_id;
        $roomId   = (int) $message->room_id;

        if(!setting('ai_chat_profiles.enable') || !setting('ai_chat_profiles.chat_provider_key_id')) return;

        $receiverId = ChatRoomMember::where('room_id', $roomId)->where('user_id', '!=', $senderId)->where('status', 'accepted')->value('user_id');
        if (!$receiverId) return;

        $subProfileId = User::where('id', $receiverId)->where('is_page', true)->where('is_active', true)->value('id');
        if (!$subProfileId) return;
        
        $persona = AiPersonaConfig::findByField('profile_id', $subProfileId);
        if (!$persona || !$persona->enabled) return;

        if (!$this->dispatcher->shouldDispatch($persona)) return;
        
        $this->dispatcher->dispatch(
            roomId: $roomId,
            persona: $persona,
            senderId: $senderId,
            triggerMessageId: (int) $message->id
        );
    }
}
