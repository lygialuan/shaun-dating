<?php

namespace Packages\ShaunSocial\Wallet\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->getDescription(),
            'created_at' => $this->created_at->setTimezone($timezone)->isoFormat(config('shaun_core.time_format.payment')),
            'gross' => $this->getGross(),
            'fee' => $this->getFee(),
            'net' => $this->getNet(),
            'extra' => $this->getExtra()
        ];
    }
}
