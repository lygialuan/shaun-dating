<?php

namespace Packages\ShaunSocial\AiChatProfiles\Contracts;

use Packages\ShaunSocial\AiChatProfiles\DataObjects\AiChatRequest;
use Packages\ShaunSocial\AiChatProfiles\DataObjects\AiChatResponse;

interface AiProviderInterface
{
    public function name(): string;

    public function chat(AiChatRequest $request): AiChatResponse;
}
