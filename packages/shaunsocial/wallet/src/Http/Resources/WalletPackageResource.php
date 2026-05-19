<?php

namespace Packages\ShaunSocial\Wallet\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletPackageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'google_price_id' => $this->google_price_id,
            'apple_price_id' => $this->apple_price_id
        ];
    }
}
