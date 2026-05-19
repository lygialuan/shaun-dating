<?php

namespace Packages\ShaunSocial\AiChatProfiles\DataObjects;

final class AiChatResponse
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public readonly string $content,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $tokensPrompt = 0,
        public readonly int $tokensCompletion = 0,
        public readonly int $latencyMs = 0,
        public readonly bool $flagged = false,
        public readonly ?string $flagReason = null,
        public readonly ?string $finishReason = null,
        public readonly array $raw = [],
    ) {
    }

    public function tokensTotal(): int
    {
        return $this->tokensPrompt + $this->tokensCompletion;
    }
}
