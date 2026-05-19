<?php


namespace Packages\ShaunSocial\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatItemResource extends JsonResource
{
    public function toArray($request)
    {        
        $subject = $this->getSubject();

        if ($subject && $this->subject_type == 'posts') {
            $user = $this->getUser();
            if ($user) {   
                $refCode = $user ? $user->ref_code : '';
                $subject->setRefCode($refCode);
            }
            $subject->setSimpleResuouce(true);
        }
        
        return [
            'id' => $this->id,
            'message_id' => $this->message_id, 
            'subject' => $subject ? $this->getSubjectResource()->toArray($request) : null,
        ];
    }
}
