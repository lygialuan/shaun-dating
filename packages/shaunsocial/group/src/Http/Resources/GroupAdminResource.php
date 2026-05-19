<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Group\Models\GroupMember;

class GroupAdminResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $owner = GroupMember::checkOwner($viewer->id, $this->group_id);

        $user = $this->getUser();
        return [
            'id' => $this->id,
            'user' => new UserResource($user),
            'canRemove' => $viewer->id == $this->user_id || $owner
        ];
    }
}
