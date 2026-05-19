<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Notification;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');

        $count = $this->getCountHash();
        $result = [
            'id' => $this->id,
            'package' => $this->package,
            'type' => $this->getClassNotification()->getType(),
            'from' => new UserResource($this->getFrom()),
            'params' => $this->getParams(),
            'count' => $count,
            'extra' => $this->getClassNotification()->getExtra(),
            'created_at' => $this->created_at->setTimezone($timezone)->diffForHumans(),
            'message' => $this->getClassNotification()->getMessage($count),
            'is_seen' => $this->is_seen,
            'href' => $this->getHref(),
            'is_system' => $this->is_system,
            'create_at_timestamp' => $this->created_at->timestamp,
            'created_at_full' => $this->created_at->setTimezone($timezone)->isoFormat(config('shaun_core.time_format.payment'))
        ];
        
        return $result;
    }
}
