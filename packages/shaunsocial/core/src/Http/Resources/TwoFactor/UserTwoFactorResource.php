<?php


namespace Packages\ShaunSocial\Core\Http\Resources\TwoFactor;

use Illuminate\Http\Resources\Json\JsonResource;

class UserTwoFactorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'params' => $this->getParams(),
            'provider' => new TwoFactorProviderResource($this->getProvider())
        ];
    }
}
