<?php


namespace Packages\ShaunSocial\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        if ($this->getViewer()) {
            $viewer = $this->getViewer();
        }
        
        $member = $this->getMember($viewer->id);
        return [
            'id' => $this->id,
            'name' => $this->getName($viewer),
            'href' => $this->getHref(),
            'members' => ChatUserResource::collection($this->getMembersUser()),
            'last_message' => new ChatMessageResource($this->getLastMessage($viewer->id)),
            'message_count' => $member->message_count,
            'is_group' => $this->is_group,
            'is_online' => $this->isOnline($viewer->id),
            'enable_notify' => $member->enable_notify,
            'last_update' => strtotime($member->last_updated_at),
            'enable' => $this->checkEnable($viewer)
        ];
    }
}
