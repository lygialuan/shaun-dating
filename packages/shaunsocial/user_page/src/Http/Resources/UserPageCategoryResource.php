<?php


namespace Packages\ShaunSocial\UserPage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPageCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttributeValue('name'),
            'childs' => $this->parent_id ? [] : UserPageCategoryResource::collection($this->childs)
        ];
    }
}
