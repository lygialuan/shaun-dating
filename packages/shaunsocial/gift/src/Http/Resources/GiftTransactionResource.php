<?php

namespace Packages\ShaunSocial\Gift\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Gift\Http\Resources\GiftResource;

class GiftTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'gift' => new GiftResource($this->gift),
            'name' => $this->sender->name,
            'date' => $this->created_at->format('F j, Y'),
        ];
    }
}