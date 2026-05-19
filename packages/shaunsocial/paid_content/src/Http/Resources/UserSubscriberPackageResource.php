<?php


namespace Packages\ShaunSocial\PaidContent\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriberPackageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'is_default' => $this->is_default,
            'package' => new SubscriberPackageResource($this->getPackage())
        ];
    }
}
