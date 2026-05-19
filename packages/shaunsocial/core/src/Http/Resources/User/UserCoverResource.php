<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCoverResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'cover' => $this->getCover()
        ];
    }
}
