<?php


namespace Packages\ShaunSocial\Gateway\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GatewayResource extends JsonResource
{
    public function toArray($request)
    {        
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
