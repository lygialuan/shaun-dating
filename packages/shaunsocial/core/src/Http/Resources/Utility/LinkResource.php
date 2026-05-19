<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Utility;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\StorageFileResource;

class LinkResource extends JsonResource
{
    public function toArray($request)
    {
        $photo = $this->getFile('photo_file_id');
        $result = [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'description' => $this->description,
            'photo' => $photo ? new StorageFileResource($photo) : null,
            'youtube_id' => $this->youtube_id,
            'tiktok_id' => $this->tiktok_id
        ];

        return $result;
    }
}
