<?php


namespace Packages\ShaunSocial\UserPage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getName(),
            'user_name' => $this->user_name,
            'avatar' => $this->getAvatar(),
            'href' => $this->getHref(),
            'is_verify' => $this->isVerify()
        ];
    }
}
