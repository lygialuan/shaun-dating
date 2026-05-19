<?php


namespace Packages\ShaunSocial\Story\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StorySongResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_url' => $this->getUrl()
        ];
    }
}
