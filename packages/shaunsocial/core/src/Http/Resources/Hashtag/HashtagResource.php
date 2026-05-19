<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Hashtag;

use Illuminate\Http\Resources\Json\JsonResource;

class HashtagResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $followed = false;
        
        if ($viewer && $this->id) {
            $followed = $viewer->getHashtagFollow($this->name) ? true : false;
        }

        return [
            'name' => $this->name,
            'post_count' => $this->post_count,
            'is_followed' => $followed,
        ];
    }
}
