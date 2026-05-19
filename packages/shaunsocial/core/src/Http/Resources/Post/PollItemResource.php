<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;

class PollItemResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_voted' => $this->getVote($viewerId) ? true : false,
            'percent' => $this->getPollItemPercent()
        ];
    }
}
