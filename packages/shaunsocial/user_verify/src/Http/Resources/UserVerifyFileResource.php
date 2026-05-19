<?php

namespace Packages\ShaunSocial\UserVerify\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserVerifyFileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'file_url' => $this->getFile('file_id')->getUrl(),
            'name' => $this->getFile('file_id')->name,
        ];
    }
}
