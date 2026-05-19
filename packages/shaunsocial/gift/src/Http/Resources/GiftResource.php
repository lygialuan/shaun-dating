<?php

namespace Packages\ShaunSocial\Gift\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftResource extends JsonResource {
    public function toArray($request) {
        return [
            'id'    => $this->id,
            'name'  => $this->getTranslatedAttributeValue('name'),
            'icon'  => $this->getIconUrl(),
            'price' => $this->getPrice(),
        ];
    }
} 