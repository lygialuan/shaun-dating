<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Like;

use Illuminate\Http\Resources\Json\JsonResource;

class LikeResource extends JsonResource
{
    public function toArray($request)
    {
        return $this->getUserResource();
    }
}
