<?php

namespace Packages\ShaunSocial\Core\Http\Resources\Post;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $isClosed = $this->isClosed();
        $result = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'is_closed' => $isClosed,
            'poll_items' => PollItemResource::collection($this->getItems()),
            'left_time' => $this->getLeftTime(),
            'canVote' => $this->canVote($viewerId) || $isAdmin,
            'vote_count' => $this->vote_count
        ];

        return $result;
    }
}
