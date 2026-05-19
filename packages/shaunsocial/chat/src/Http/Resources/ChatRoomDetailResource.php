<?php


namespace Packages\ShaunSocial\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $member = $this->getMember($viewer->id);
        $userStatus = '';
        if (! $this->is_group) {
            $members = $this->getMembers();
            $user = $members->first(function ($member, $key) use ($viewer) {
                return ($viewer->id != $member->user_id);
            });
            $userStatus = $user->status;
        }        
        
        return [
            'id' => $this->id,
            'name' => $this->getName($viewer),
            'members' => ChatMessageUserResource::collection($this->getMembers()),
            'is_group' => $this->is_group,
            'enable_notify' => $member->enable_notify,
            'status' => $member->status,
            'is_online' => $this->isOnline($viewer->id),
            'is_owner' => $member->is_owner,
            'is_moderator' => $member->is_moderator,
            'user_status' => $userStatus,
            'last_message' => new ChatMessageResource($this->getLastMessage($viewer->id)),
            'last_update' => strtotime($member->last_updated_at),
            'enable' => $this->checkEnable($viewer)
        ];
    }
}
