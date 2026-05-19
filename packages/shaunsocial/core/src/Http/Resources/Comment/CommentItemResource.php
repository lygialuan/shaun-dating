<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Comment;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentItemResource extends JsonResource
{
    public function toArray($request)
    {        
        $subject = $this->getSubject();
        return [
            'id' => $this->id,
            'comment_id' => $this->comment_id, 
            'subject' => $subject ? $this->getSubjectResource()->toArray($request) : null,
        ];
    }
}
