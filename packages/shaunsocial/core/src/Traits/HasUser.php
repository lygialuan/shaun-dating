<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;

trait HasUser
{
    public function getUser($includeDelete = false)
    {
        $user = User::findByField('id', $this->user_id);
        if ($includeDelete) {
            return getUserIncludeDelete($user);
        } else {
            return $user;
        }
    }

    public function getUserResource()
    {
        return new UserResource($this->getUser());
    }

    public function isOwner($userId)
    {
        return $this->user_id == $userId;
    }
}
