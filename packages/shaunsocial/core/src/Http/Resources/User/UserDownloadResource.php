<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDownloadResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');

        return [
            'downloadLink' => $this->id ? $this->getDownloadLink() : '',
            'downloadDate' => $this->file_id ? $this->getFile('file_id')->created_at->setTimezone($timezone)->diffForHumans() : '',
            'downloadDateTimestamp' => $this->file_id ? $this->getFile('file_id')->created_at->timestamp : '',
            'canRequest' => $this->id ? $this->canDownload() : true
        ];
    }
}
