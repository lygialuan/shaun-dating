<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;

class UserBlockResource extends JsonResource
{
    public function toArray($request)
    {
        $user = User::findByField('id', $this->blocker_id);

        return new UserResource($user);
    }
}
