<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Comment;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentReplyItemResource extends JsonResource
{
    public function toArray($request)
    {        
        $subject = $this->getSubject();
        return [
            'id' => $this->id,
            'reply_id' => $this->reply_id, 
            'subject' => $subject ? $this->getSubjectResource()->toArray($request) : null,
        ];
    }
}
