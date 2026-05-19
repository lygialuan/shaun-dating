<?php


namespace Packages\ShaunSocial\Vibb\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VibbSongResource extends JsonResource
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
