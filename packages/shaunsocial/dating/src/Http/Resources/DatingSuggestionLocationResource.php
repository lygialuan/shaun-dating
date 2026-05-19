<?php

namespace Packages\ShaunSocial\Dating\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DatingSuggestionLocationResource extends JsonResource {
    public function toArray($request) {
        return [
            'id'   => $this->id,
            'name' => $this->address,
        ];
    }
} 