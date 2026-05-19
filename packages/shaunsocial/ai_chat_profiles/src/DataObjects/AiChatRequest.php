<?php

namespace Packages\ShaunSocial\AiChatProfiles\DataObjects;

use InvalidArgumentException;

final class AiChatRequest
{
    /**
     * @param  AiChatMessage[]  $messages
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly array $messages,
        public readonly ?string $model = null,
        public readonly ?float $temperature = null,
        public readonly ?int $maxTokens = null,
        public readonly ?int $timeoutSec = null,
        public readonly array $metadata = [],
    ) {
        if ($messages === []) {
            throw new InvalidArgumentException('AiChatRequest requires at least one message.');
        }

        foreach ($messages as $message) {
            if (! $message instanceof AiChatMessage) {
                throw new InvalidArgumentException('Messages must be AiChatMessage instances.');
            }
        }
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    public function messagesArray(): array
    {
        return array_map(static fn (AiChatMessage $m) => $m->toArray(), $this->messages);
    }
}
