<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class PhotoVerifyItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'photo' => $this->getFile()?->getUrl(),
            'order' => $this->order,
            'is_thumbnail' => $this->is_thumbnail,
            'status' => $this->status,
        ];
    }
}
