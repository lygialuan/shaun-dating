<?php


namespace Packages\ShaunSocial\Core\Http\Resources\ContentWarning;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentWarningCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttributeValue('name')
        ];
    }
}
