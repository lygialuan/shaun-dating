<?php


namespace Packages\ShaunSocial\PaidContent\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TipPackageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount
        ];
    }
}
