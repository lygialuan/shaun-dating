<?php

namespace Packages\ShaunSocial\AiChatProfiles\Services;

use Carbon\Carbon;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatJobStatus;
use Packages\ShaunSocial\AiChatProfiles\Jobs\ProcessAiChatJob;
use Packages\ShaunSocial\AiChatProfiles\Models\AiChatJob;
use Packages\ShaunSocial\AiChatProfiles\Models\AiPersonaConfig;

class AiChatJobDispatcher
{
    public function dispatch(int $roomId, AiPersonaConfig $persona, int $senderId, int $triggerMessageId): AiChatJob
    {
        $delayMin = (int) ($persona->reply_delay_min_sec ?? 10);
        $delayMax = (int) ($persona->reply_delay_max_sec ?? 120);
        $scheduledAt = Carbon::now()->addSeconds(rand($delayMin, $delayMax));

        $job = AiChatJob::create([
            'room_id'            => $roomId,
            'profile_id'         => $persona->profile_id,
            'sender_id'          => $senderId,
            'trigger_message_id' => $triggerMessageId,
            'status'             => AiChatJobStatus::PENDING,
            'scheduled_at'       => $scheduledAt,
            'attempts'           => 0,
        ]);
        
        ProcessAiChatJob::dispatch($job->id)->delay($scheduledAt);

        return $job;
    }

    /**
     * Check whether an AI reply/suggestion should be dispatched for the given profile.
     * Returns false if daily cap is reached.
     */
    public function shouldDispatch(AiPersonaConfig $persona): bool
    {
        $maxDaily = (int) ($persona->max_messages_per_day ?? 50);

        $sentToday = AiChatJob::where('profile_id', $persona->profile_id)->where('status', AiChatJobStatus::SENT)->whereDate('finished_at', today())->count();

        return $sentToday < $maxDaily;
    }
}
