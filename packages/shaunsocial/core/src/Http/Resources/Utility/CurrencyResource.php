<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Utility;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'symbol' => $this->symbol,
            'name' => $this->name
        ];
    }
}
