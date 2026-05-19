<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Utility;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\StorageFileResource;

class VideoResource extends JsonResource
{
    public function toArray($request)
    {
        $thumb = $this->getFile('thumb_file_id');
        $file = $this->getFile('file_id');
        $result = [
            'file' => new StorageFileResource($file),
            'thumb' => new StorageFileResource($thumb),
            'duration' => $this->duration
        ];

        return $result;
    }
}
