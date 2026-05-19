<?php

namespace Packages\ShaunSocial\Dating\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DatingAttributeResource extends JsonResource {
    public function toArray($request) {
        $attributeValues = $this->getAttributeValues($this->id);
        $viewer = $request->user();

        $attributeValues = $attributeValues->filter(function ($value) use ($viewer) {
            return $value->canUse();
        });
        
        return [
            'id'                => $this->id,
            'name'              => $this->getTranslatedAttributeValue('name'),
            'icon'              => $this->getIconUrl(),
            'allow_multiple'    => $this->allow_multiple,
            'attribute_values'  => $attributeValues ? DatingAttributeValueResource::collection($attributeValues) : [],
        ];
    }
} 