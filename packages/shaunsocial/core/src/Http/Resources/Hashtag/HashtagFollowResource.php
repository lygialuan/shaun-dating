<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Hashtag;

use Illuminate\Http\Resources\Json\JsonResource;

class HashtagFollowResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'post_count' => $this->getHashtag()->post_count,
        ];
    }
}
