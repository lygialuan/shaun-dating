<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupCoverResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'cover' => $this->getCover()
        ];
    }
}
