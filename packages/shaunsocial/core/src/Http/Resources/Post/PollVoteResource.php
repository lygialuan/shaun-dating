<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;

class PollVoteResource extends JsonResource
{
    public function toArray($request)
    {
        return $this->getUserResource();
    }
}
