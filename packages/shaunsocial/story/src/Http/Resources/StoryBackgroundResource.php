<?php


namespace Packages\ShaunSocial\Story\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoryBackgroundResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'photo_url' => $this->getPhotoUrl()
        ];
    }
}
