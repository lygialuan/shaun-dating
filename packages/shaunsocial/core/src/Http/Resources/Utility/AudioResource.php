<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Utility;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\StorageFileResource;

class AudioResource extends JsonResource
{
    public function toArray($request)
    {
        $file = $this->getFile('file_id');
        $result = [
            'file' => new StorageFileResource($file),
            'duration' => $this->duration
        ];

        return $result;
    }
}
