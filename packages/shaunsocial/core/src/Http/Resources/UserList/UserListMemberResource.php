<?php


namespace Packages\ShaunSocial\Core\Http\Resources\UserList;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;

class UserListMemberResource extends JsonResource
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
