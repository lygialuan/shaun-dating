<?php


namespace Packages\ShaunSocial\Vibb\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VibbPostSongResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
