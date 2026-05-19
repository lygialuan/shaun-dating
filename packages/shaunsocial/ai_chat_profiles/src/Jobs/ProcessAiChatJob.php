<?php

namespace Packages\ShaunSocial\AiChatProfiles\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Packages\ShaunSocial\AiChatProfiles\Contracts\AiProviderInterface;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatJobStatus;
use Packages\ShaunSocial\AiChatProfiles\Models\AiChatJob;
use Packages\ShaunSocial\AiChatProfiles\Models\AiMessageLog;
use Packages\ShaunSocial\AiChatProfiles\Models\AiPersonaConfig;
use Packages\ShaunSocial\AiChatProfiles\Support\AiPromptBuilder;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Repositories\Api\ChatRepository;
use Packages\ShaunSocial\Core\Models\User;
use Throwable;

class ProcessAiChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly int $jobId)
    {
    }

    public function handle(AiProviderInterface $provider, AiPromptBuilder $promptBuilder, ChatRepository $chatRepository): void
    {
        $job = AiChatJob::find($this->jobId);
        if (! $job || $job->status !== AiChatJobStatus::PENDING) {
            return;
        }

        $job->update([
            'status'     => AiChatJobStatus::RUNNING,
            'started_at' => now(),
            'attempts'   => $job->attempts + 1,
        ]);

        try {
            $persona = AiPersonaConfig::findByField('profile_id', $job->profile_id);

            $history = ChatMessage::where('room_id', $job->room_id)
                ->where('type', 'text')
                ->where('is_delete', false)
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get()
                ->reverse()
                ->values()
                ->all();

            $sender = User::findByField('id', $job->sender_id);
            $profile = User::findByField('id', $job->profile_id);

            $request = $promptBuilder->build($persona, $profile, $history, $sender);

            $aiResponse = $provider->chat($request);
            if(!$aiResponse->content){
                AiChatJob::where('id', $this->jobId)->update([
                    'status'      => AiChatJobStatus::FAILED,
                    'last_error'  => "Null content",
                    'finished_at' => now(),
                ]);
                return;
            }

            $data = [
                'type' => 'text',
                'content' => $aiResponse->content,
                'items' => [],
                'room_id' =>  $job->room_id,
                'parent_message_id' => 0
            ];

            $chatResult = $chatRepository->store_room_message($data, $profile);

            AiMessageLog::create([
                'room_id'           => $job->room_id,
                'profile_id'        => $job->profile_id,
                'job_id'            => $job->id,
                'chat_message_id'   => $chatResult['message']['id'],
                'provider'          => $aiResponse->provider,
                'model'             => $aiResponse->model,
                'tokens_prompt'     => $aiResponse->tokensPrompt,
                'tokens_completion' => $aiResponse->tokensCompletion,
                'latency_ms'        => $aiResponse->latencyMs,
                'flagged'           => $aiResponse->flagged,
                'flag_reason'       => $aiResponse->flagReason,
                'prompt_preview'    => mb_substr($request->messages[0]->content, 0, 500),
                'reply_preview'     => mb_substr($aiResponse->content, 0, 500),
            ]);

            $job->update([
                'status'           => AiChatJobStatus::SENT,
                'reply_message_id' => $chatResult['message']['id'],
                'finished_at'      => now(),
            ]);
        } catch (Throwable $e) {
            $exhausted = $this->attempts() >= $this->tries;
            $retryable = property_exists($e, 'retryable') ? $e->retryable : true;

            if ($exhausted || ! $retryable) {
                $job->update([
                    'status'      => AiChatJobStatus::FAILED,
                    'last_error'  => $e->getMessage(),
                    'finished_at' => now(),
                ]);
            } else {
                $job->update([
                    'status'      => AiChatJobStatus::FAILED,
                    'last_error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    public function failed(Throwable $e): void
    {
        AiChatJob::where('id', $this->jobId)->update([
            'status'      => AiChatJobStatus::FAILED,
            'last_error'  => $e->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
