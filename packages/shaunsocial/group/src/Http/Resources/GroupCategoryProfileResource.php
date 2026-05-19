<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupCategoryProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttributeValue('name')
        ];
    }
}
