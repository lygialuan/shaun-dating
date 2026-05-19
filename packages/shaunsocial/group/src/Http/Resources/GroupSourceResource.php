<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupSourceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTitle(),
            'href' => $this->getHref(),
            'source_type' => $this->getSubjectType(),
            'slug' => $this->slug,
        ];
    }
}
