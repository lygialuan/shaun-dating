<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttributeValue('name'),
            'childs' => $this->parent_id ? [] : GroupCategoryResource::collection($this->childs)
        ];
    }
}
