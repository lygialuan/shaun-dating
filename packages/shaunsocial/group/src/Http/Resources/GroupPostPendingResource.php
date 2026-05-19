<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;

class GroupPostPendingResource extends JsonResource
{
    public function toArray($request)
    {
        $post = $this->getPost();
        $post->setIn('source', true);
        
        return [
            'id' => $this->id,
            'post' => new PostResource($post)
        ];
    }
}
