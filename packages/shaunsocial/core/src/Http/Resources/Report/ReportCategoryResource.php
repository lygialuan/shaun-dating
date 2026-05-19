<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttributeValue('name')
        ];
    }
}
