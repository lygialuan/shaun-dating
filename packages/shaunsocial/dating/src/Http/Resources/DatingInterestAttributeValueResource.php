<?php

namespace Packages\ShaunSocial\Dating\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Dating\Models\DatingInterestAttribute;

class DatingInterestAttributeValueResource extends JsonResource {
    
    public function toArray($request) {
        if (!$this->resource) return [];

        $attribute = DatingInterestAttribute::findByField('id', $this->attribute_id);

        if(!$attribute?->is_active) return [];

        return [
            'id'            => $this->id,
            'name'          => $this->getTranslatedAttributeValue('name'),
            'icon'          => $attribute?->getIconUrl(),
            'category_name' => $attribute?->getTranslatedAttributeValue('name'),
        ];
    }
}