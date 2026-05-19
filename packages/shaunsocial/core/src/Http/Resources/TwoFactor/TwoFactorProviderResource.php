<?php


namespace Packages\ShaunSocial\Core\Http\Resources\TwoFactor;

use Illuminate\Http\Resources\Json\JsonResource;

class TwoFactorProviderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttributeValue('name'),
            'type' => $this->type,
            'description' => $this->getTranslatedAttributeValue('description')
        ];
    }
}
