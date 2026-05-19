<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');
        
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'created_at' => $this->created_at->setTimezone($timezone)->diffForHumans(),
            'subject' => $this->getSubjectResource(),
            'subject_type' => $this->subject_type,
            'create_at_timestamp' => $this->created_at->timestamp
        ];
    }
}
