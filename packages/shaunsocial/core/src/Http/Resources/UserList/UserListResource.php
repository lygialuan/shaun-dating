<?php


namespace Packages\ShaunSocial\Core\Http\Resources\UserList;

use Illuminate\Http\Resources\Json\JsonResource;

class UserListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => 'list',
            'member_count' => $this->member_count
        ];
    }
}
