<?php


namespace Packages\ShaunSocial\PaidContent\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriberPackageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'description' => $this->getDescription()
        ];
    }
}
