# Quy Trình Hoạt Động Chat Auto AI (AiChatProfiles)

## **1. Trigger (Nhận tin nhắn)**
```
User gửi TEXT message → ChatMessage::created()
↓
AiChatMessageObserver->created(ChatMessage $message)
```
**Conditions:**
- `setting('ai_chat_profiles.enable') = true`
- `setting('ai_chat_profiles.chat_provider_key_id') > 0`
- Receiver là AI profile (`is_page = true`)
- AiPersonaConfig tồn tại cho profile

## **2. Dispatch Job (AiChatJobDispatcher)**
```
AiChatJobDispatcher->dispatch(roomId, profileId, senderId, triggerMessageId, mode)
↓
Tạo AiChatJob (PENDING, scheduledAt = random 10-120s delay)
↓
ProcessAiChatJob::dispatch($jobId)->delay($scheduledAt)
```

## **3. Job Execute (ProcessAiChatJob->handle())**
```
AiChatJob status = RUNNING
↓
Lấy AiPersonaConfig (prompt, tone, mode...)
↓
Lấy chat history (20 tin gần nhất)
↓
AiPromptBuilder->build() → AiChatRequest
↓
$provider->chat($request)  // ← AI PROVIDER (OpenAI/Claude/...)
↓
Tạo ChatMessage reply từ AI response
↓
Update room.last_message_id, ChatMessageUser (read status)
↓
Log AiMessageLog (tokens, latency, provider...)
↓
AiChatJob status = SENT
```

## **4. Provider Resolution (DI Flow)**
```
ProcessAiChatJob injects AiProviderInterface
↓ (ApplicationServiceProvider bind)
AiProviderManager->driverFromSetting()
↓
AiProviderKey $key = find(setting('ai_chat_profiles.chat_provider_key_id'))
↓
new AiProviderKeyBridge($key, $key->provider->getProviderInstance())
↓ (AiProvider->getProviderInstance())
app('Packages\ShaunSocial\AiProvider\Providers\OpenAIProvider')
↓
Bridge->chat() → Delegate->sendMessage() → OpenAI API!
```

## **5. Features & Limits**
- **Delay**: 10-120s random (human-like)
- **Daily cap**: `max_daily_per_profile` (default 50)
- **Retry**: 3 lần, backoff 60s
- **Modes**: AUTO (auto reply), ASSIST (suggestion)
- **Multi-provider**: OpenAI, Claude, Gemini, Grok, Perplexity
- **Tracking**: Tokens usage, latency, errors in AiMessageLog

## **Admin Config**
```
- ai_chat_profiles.enable
- ai_chat_profiles.chat_provider_key_id (AiProviderKey ID)
- ai_chat_profiles.reply_delay_min_sec / max_sec
- ai_chat_profiles.max_daily_per_profile
- AiPersonaConfig per profile (system prompt, tone...)
```

**End-to-end**: User message → Job queue → AI response → ChatMessage tự động!
