<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Invite;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Enum\InviteType;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;

class InviteResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');
        
        switch ($this->type) {
            case InviteType::INVITE:
                return [
                    'email' => $this->email,
                    'status' => $this->getStatus(),
                    'sent_at' => $this->updated_at->setTimezone($timezone)->diffForHumans(),
                    'sent_at_timestamp' => $this->updated_at->timestamp            
                ];
                break;
            case InviteType::REFERRAL:
                $user = User::findByField('id', $this->new_user_id);
                if (! $user) {
                    $user = getDeleteUser();
                }

                return new UserResource($user);
                break;
        }
        
    }
}
