<?php

namespace Packages\ShaunSocial\AiChatProfiles\Repositories\Api;

use Packages\ShaunSocial\AiChatProfiles\Models\AiChatSuggestion;

class AiSuggestionRepository
{
    public function suggestion($viewerId, $roomId)
    {
        $suggestion = AiChatSuggestion::where('room_id', $roomId)->where('profile_id', $viewerId)->latest('created_at')->first();

        return $suggestion?->suggestion_text ?? null;
    }
}
