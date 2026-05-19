<?php


namespace Packages\ShaunSocial\UserPage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;

class UserPageReviewResource extends JsonResource
{
    public function toArray($request)
    {
        $page = $this->getPage();
        if (! $page) {
            $page = getDeletePage();
        }
        return [
            'is_recommend' => $this->is_recommend,
            'page' => new UserResource($page)
        ];
    }
}
