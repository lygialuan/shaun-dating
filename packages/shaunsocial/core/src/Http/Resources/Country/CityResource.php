<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Country;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttributeValue('name')
        ];
    }
}
