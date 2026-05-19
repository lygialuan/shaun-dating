<?php


namespace Packages\ShaunSocial\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Traits\Utility;

class ChatMessageResource extends JsonResource
{
    use Utility;

    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');
        $time = $this->created_at->setTimezone($timezone);
        $viewerId = $viewer ? $viewer->id : 0;

        $createdAt = $time->isoFormat('DD MMMM YYYY, HH:mm');
        if ($this->created_at->isToday()) {
            $createdAt = $time->isoFormat('HH:mm');
        } elseif ($this->created_at->isCurrentWeek()) {
            $createdAt = $time->isoFormat('dddd HH:mm');
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' =>  $this->user_id ? new ChatUserResource($this->getUser()) : null,
            'content' => $this->makeContentHtml($this->getContent()),
            'short_content' => $this->getShortContent(),
            'type' => $this->type,
            'items' => ChatItemResource::collection($this->getItems()),
            'created_at_short' => $time->longAbsoluteDiffForHumans(['parts'=>1]),
            'created_at' => $createdAt,
            'created_at_time' => strtotime($this->created_at),
            'created_at_full' => $time->toDateTimeString(),
            'is_delete' => $this->is_delete,
            'room_id' => $this->room_id,
            'parent_message' => new ChatMessageResource($this->getParentMessage()),
            'client_message_id' => $this->getClientMessageId(),
            'canContentTranslate' => $this->supportContentTranslate('content') && $viewerId != $this->user_id,
        ];
    }
}
