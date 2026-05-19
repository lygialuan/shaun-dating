<?php

namespace Packages\ShaunSocial\Chatbot\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatbotProviderResource extends JsonResource
{
    public function toArray($request)
    {
        $provider = $this->provider;

        return [
            'id' => $this->id,
            'name' => $provider?->name ?? $this->name,
            'description' => $provider
                ? ($provider->getTranslatedAttributeValue('description') ?: $provider->description)
                : $this->getTranslatedAttributeValue('description'),
            'key' => [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->getTranslatedAttributeValue('description') ?: $this->description,
            ],
            'provider' => $provider ? [
                'id' => $provider->id,
                'name' => $provider->name,
            ] : null,
        ];
    }
}
