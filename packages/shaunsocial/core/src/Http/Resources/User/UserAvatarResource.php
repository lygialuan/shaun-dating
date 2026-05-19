<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAvatarResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'avatar' => $this->getAvatar()
        ];
    }
}
