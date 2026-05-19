<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Comment;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Traits\Utility;

class CommentReplyResource extends JsonResource
{
    use Utility;

    public function toArray($request)
    {
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');
        return [
            'id' => $this->id,
            'content' => $this->makeContentHtml($this->getMentionContent($viewer)),
            'created_at' => $this->created_at->setTimezone($timezone)->diffForHumans(),
            'user' => $this->getUserResource(),
            'like_count' => $this->like_count,
            'is_liked' => $this->getLike($viewerId) ? true : false,
            'mentions' => $this->getMentionUsersResource($viewer),
            'canDelete' => $this->canDelete($viewerId) || $isAdmin,
            'href' => $this->getHref(),
            'canEdit' => $this->canEdit($viewerId) || $isAdmin,
            'isEdited' => $this->is_edited ? true : false,
            'create_at_timestamp' => $this->created_at->timestamp,
            'items' => CommentReplyItemResource::collection($this->getItems()),
            'canReport' => $this->canReport($viewerId),
            'type' => $this->type,
            'canContentTranslate' => $this->supportContentTranslate('content') && $viewerId != $this->user_id,
        ];
    }
}
