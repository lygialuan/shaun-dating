<?php


namespace Packages\ShaunSocial\Story\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        return [
            'id' => $this->id,
            'user' => $this->getUserResource(),
            'item' => new StoryItemResource($this->getItemOnList($viewer->id)),
            'seen' => $this->checkSeen($viewer->id)
        ];
    }
}
