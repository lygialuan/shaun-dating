<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupOverviewMemberResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->getUser();
        return [
            'id' => $user->id,
            'name' => $user->getName(),
            'user_name' => $user->user_name,
            'avatar' => $user->getAvatar(),
        ];
    }
}
