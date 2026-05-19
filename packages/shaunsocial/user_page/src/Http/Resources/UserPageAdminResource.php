<?php


namespace Packages\ShaunSocial\UserPage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPageAdminResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user' => $this->getUserResource(),
            'role_name' => $this->getRoleName()
        ];
    }
}
