<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Group\Enum\GroupMemberRole;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupMemberRequest;

class GroupResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $member = GroupMember::getMember($viewerId, $this->id);
        $requestId = 0;
        if (! $member) {
            $request = GroupMemberRequest::getRequest($viewerId, $this->id);
            $requestId = $request ? $request->id : 0;
        }
        
        return [
            'id' => $this->id,
            'name' => $this->getTitle(),
            'cover' => $this->getCover(),
            'href' => $this->getHref(),
            'slug' => $this->slug,
            'categories' => GroupCategoryProfileResource::collection($this->getCategories()),
            'member_count' => $this->member_count,
            'members' => GroupOverviewMemberResource::collection($this->getOverviewMembers()),
            'canJoin' => $this->canJoin($viewerId) || ($isAdmin && ! $member),
            'is_member' => $member ? true : false,
            'is_owner' => $member ? $member->isOwner() : false,
            'is_admin' => $member ? $member->role == GroupMemberRole::ADMIN : false,
            'type' => $this->type,
            'type_text' => $this->getTypeText(),
            'request_id' => $requestId,
            'status' => $this->status
        ];
    }
}
