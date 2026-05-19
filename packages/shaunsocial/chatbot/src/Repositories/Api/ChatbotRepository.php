<?php

namespace Packages\ShaunSocial\Chatbot\Repositories\Api;

use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;
use Packages\ShaunSocial\AiProvider\Traits\ManagesAiProviderKeyStatus;
use Packages\ShaunSocial\Chatbot\Http\Resources\ChatbotProviderResource;
use Packages\ShaunSocial\Chatbot\Models\ChatbotHistory;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserActionLog;

class ChatbotRepository
{
    use ManagesAiProviderKeyStatus;

    /**
     * Send a message to the active chatbot provider and persist history.
     * Returns ['message' => array]
     */
    public function send_message(User $user, string $message)
    {
        $providerKey = $this->resolveActiveProviderKey();
        if (! $providerKey) {
            return [
                'status' => false,
                'message' => __('Chatbot provider is not configured.'),
            ];
        }

        $providerInstance = $providerKey->provider?->getProviderInstance();
        if (! $providerInstance) {
            return [
                'status' => false,
                'message' => __('Chatbot provider is unavailable.'),
            ];
        }

        $config = $providerKey->config ?? [];

        // Build context from latest 10 messages for current user
        $historyRecords = ChatbotHistory::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $contextMessages = [];
        foreach ($historyRecords as $item) {
            $rows = json_decode($item->data, true);
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (isset($row['role'], $row['content'])) {
                        $contextMessages[] = [
                            'role' => $row['role'],
                            'content' => (string) $row['content']
                        ];
                    }
                }
            }
        }

        try {
            $result = $providerInstance->sendMessage($message, $config, ['messages' => $contextMessages]);
        } catch (\Throwable $e) {
            $this->handleAiProviderKeyFailure($providerKey, $e->getMessage(), 3, [
                'source' => 'chatbot.send_message.exception',
            ]);
            return [
                'status' => false,
                'message' => __('Chatbot provider is unavailable.'),
            ];
        }

        if (! $result['status']) {
            $this->handleAiProviderKeyFailure($providerKey, $result['message'] ?? '', 3, [
                'source' => 'chatbot.send_message.response',
            ]);
            return $result;
        }

        $this->handleAiProviderKeySuccess($providerKey);

        // Persist to history and log action
        ChatbotHistory::create([
            'user_id' => $user->id,
            'data' => json_encode([
                [
                    'role' => 'user',
                    'content' => $message,
                ],
                [
                    'role' => 'assistant',
                    'content' => $result['data']['response'] ?? '',
                ],
            ]),
        ]);

        UserActionLog::create([
            'user_id' => $user->id,
            'type' => 'send_message_chatbot',
        ]);

        return [
            'status' => true,
            'message' => $result['data'] ?? [],
        ];
    }

    /**
     * Get merged history messages for a user with pagination meta.
     * Returns ['message' => array, 'has_next_page' => bool]
     */
    public function get_history(User $user, int $page = 1)
    {
        $builder = ChatbotHistory::where('user_id', $user->id)->orderBy('id', 'DESC');
        $histories = ChatbotHistory::getCachePagination('chat_bot_history_'.$user->id, $builder, $page);
        $historiesNextPage = ChatbotHistory::getCachePagination('chat_bot_history_'.$user->id, $builder, $page + 1);

        $data = [];
        foreach ($histories as $item) {
            $rows = json_decode($item->data, true);
            if (is_array($rows)) {
                $data[] = $rows[1];
                $data[] = $rows[0];
            }
        }

        return [
            'items' => $data,
            'has_next_page' => count($historiesNextPage) > 0,
        ];
    }

    /**
     * Get current provider with minimal info.
     */
    public function get_provider()
    {
        $providerKey = $this->resolveActiveProviderKey();
        if (! $providerKey) {
            return null;
        }

        return new ChatbotProviderResource($providerKey);
    }

    /**
     * Clear all history for a specific user.
     */
    public function clear_history(User $user)
    {
        $history = ChatbotHistory::where('user_id', $user->id)->first();
        if ($history) {
            $history->delete();
            ChatbotHistory::where('user_id', $user->id)->delete();
        }
    }

    /**
     * Resolve the active AI provider key configured for the chatbot.
     */
    protected function resolveActiveProviderKey(): ?AiProviderKey
    {
        $keyId = (int) setting('ai_features.chatbot_provider_key_id', setting('shaun_chatbot.provider_key_id', 0));
        if ($keyId) {
            $specified = AiProviderKey::with('provider')->find($keyId);
            if ($specified && $specified->is_active && $specified->status === 'healthy' && $specified->provider) {
                return $specified;
            }
        }

        return null;
    }
}
