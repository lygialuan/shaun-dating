<?php

namespace Packages\ShaunSocial\Dating\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserProfileResource;

class DatingSwipeResource extends JsonResource
{
    protected const ACTION_USER_MAP = [
        'liked_me'  => 'user_id',
        'viewed_me' => 'user_id',
        'i_liked'   => 'target_user_id',
        'viewed'    => 'target_user_id',
    ];

    public function toArray($request)
    {
        $action = $request->action;
        $field  = self::ACTION_USER_MAP[$action] ?? null;

        return [
            'id'   => $this->id,
            'user' => $field ? new UserProfileResource($this->getUser($this->{$field})) : null,
        ];
    }
}
