<?php


namespace Packages\ShaunSocial\Core\Http\Resources\History;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Traits\Utility;

class HistoryResource extends JsonResource
{
    use Utility;
    
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');

        return [
            'id' => $this->id,
            'content' => $this->makeContentHtml($this->getContent($viewer)),
            'user' => $this->getUserResource(),
            'mentions' => $this->getMentionUsersResource($viewer),
            'created_at' => $this->created_at->setTimezone($timezone)->diffForHumans(),
            'create_at_timestamp' => $this->created_at->timestamp
        ];
    }
}
