<?php


namespace Packages\ShaunSocial\Story\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoryDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $canSendMessage = $this->getUser()->canSendMessage($viewerId) || $isAdmin;

        $items = $this->getItems();
        return [
            'id' => $this->id,
            'user' => $this->getUserResource(),
            'items' => StoryItemResource::collection($items),
            'item_view_id' => $viewerId ? $this->getItemOnList($viewerId)->id : $items->first()->id,
            'canMessage' => $viewerId == $this->user_id ? false : $canSendMessage
        ];
    }
}
