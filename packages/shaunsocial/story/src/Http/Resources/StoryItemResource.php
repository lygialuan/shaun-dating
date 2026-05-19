<?php


namespace Packages\ShaunSocial\Story\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\VideoResource;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Story\Models\StoryView;

class StoryItemResource extends JsonResource
{
    use Utility;
    
    public function toArray($request)
    {
        $background = $this->getBackground();
        $song = $this->getSong();
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');
        $photo = $this->getPhoto();
        if (! $this->story_id && $viewerId != $this->user_id) {
            return null;
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'background' => $background ? new StoryBackgroundResource($background) : null,
            'content' => $this->makeContentHtml($this->getMentionContent($viewer)),
            'song' => $song ? new StorySongResource($song) : null,
            'created_at' => $this->created_at->setTimezone($timezone)->diffForHumans(),
            'photo_url' => $photo ? $photo->getUrl('photo_id') : '',
            'content_color' => $this->content_color,            
            'count' => $viewerId == $this->user_id ? StoryView::getCount($this->user_id, $this->id) : 0,            
            'canDelete' => $this->canDelete($viewerId) || $isAdmin,
            'story_id' => $this->story_id,
            'user' => [
                'name' => $this->getUser()->getName(),
            ],
            'canReport' => $this->canReport($viewerId),
            'canShare' => $this->canShare(),
            'mentions' => $this->getMentionUsersResource($viewer),
            'video' => $this->video_id ? new VideoResource($this->getVideo()) : null
        ];
    }
}
