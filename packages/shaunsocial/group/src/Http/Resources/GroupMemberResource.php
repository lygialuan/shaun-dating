<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;

class GroupMemberResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->getUser();
        return [
            'id' => $this->id,
            'user' => new UserResource($user)
        ];
    }
}
