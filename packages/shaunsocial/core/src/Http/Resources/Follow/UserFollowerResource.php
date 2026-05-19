<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Follow;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;

class UserFollowerResource extends JsonResource
{
    public function toArray($request)
    {
        $user = User::findByField('id', $this->user_id);

        return new UserResource($user);
    }
}
