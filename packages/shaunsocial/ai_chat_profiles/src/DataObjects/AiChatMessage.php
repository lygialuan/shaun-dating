<?php

namespace Packages\ShaunSocial\AiChatProfiles\DataObjects;

use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatRole;

final class AiChatMessage
{
    public function __construct(
        public readonly AiChatRole $role,
        public readonly string $content,
    ) {
    }

    public static function system(string $content): self
    {
        return new self(AiChatRole::SYSTEM, $content);
    }

    public static function user(string $content): self
    {
        return new self(AiChatRole::USER, $content);
    }

    public static function assistant(string $content): self
    {
        return new self(AiChatRole::ASSISTANT, $content);
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }
}
