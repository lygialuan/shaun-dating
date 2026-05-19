<?php


namespace Packages\ShaunSocial\GatewayRecurring\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GatewayRecurringResource extends JsonResource
{
    public function toArray($request)
    {        
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
