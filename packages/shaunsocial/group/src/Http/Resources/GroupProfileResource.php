<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Hashtag\HashtagResource;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Group\Models\GroupHashtagTrending;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupMemberRequest;
use Packages\ShaunSocial\Group\Models\GroupPostPending;

class GroupProfileResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');

        $member = GroupMember::getMember($viewerId, $this->id);
        $isGroupAdmin = $member ? $member->isAdmin() : false;
        $isGroupOwner = $member ? $member->isOwner() : false;
        $requestId = 0;
        if (! $member) {
            $request = GroupMemberRequest::getRequest($viewerId, $this->id);
            $requestId = $request ? $request->id : 0;
        }

        $canView = $this->canView($viewerId) || $isAdmin;
        $owner = GroupMember::getOwner($this->id);
        $data = [
            'id' => $this->id,
            'name' => $this->getTitle(),
            'cover' => $this->getCover(),
            'href' => $this->getHref(),
            'description' => $this->description,
            'created_at' => $this->created_at->setTimezone($timezone)->isoFormat(config('shaun_core.time_format.payment')),
            'categories' => GroupCategoryProfileResource::collection($this->getCategories()),
            'member_count' => $this->member_count,
            'admin_count' => $this->admin_count,
            'member_without_admin' => $this->member_count - 1 - $this->admin_count,
            'members' => GroupOverviewMemberResource::collection($this->getOverviewMembers()),
            'is_member' => $member ? true : false,
            'is_admin' => $isGroupAdmin,
            'is_owner' => $isGroupOwner,
            'user_post_pending_count' => GroupPostPending::getCountByUser($viewerId, $this->id),
            'canPostStatus' => $this->getPostStatus($viewerId),
            'canJoin' => $this->canJoin($viewerId) || ($isAdmin && ! $member && !$requestId),
            'canView' => $canView,
            'type' => $this->type,
            'type_text' => $this->getTypeText(),
            'canLeave' => ($member && ! $member->isOwner()) ? true : false,
            'hashtags' => HashtagResource::collection($this->getHashtags()),
            'description' => $this->description,
            'request_id' => $requestId,
            'statistics' => $this->getStatisticOnDetail(),
            'owner' => new GroupMemberResource($owner),
            'slug' => $this->slug,
            'canReport' => $viewerId && !$isGroupOwner
        ];

        if ($isGroupAdmin) {
            $data += [
                'member_request_count' => $this->member_request_count,
                'post_pending_count' => $this->post_pending_count,
                'block_count' => $this->block_count
            ];
        }
        if ($member) {
            $data += ['notify_settings' => ($member ? $member->getNotifySetting() : [])];
        }

        if ($canView) {
            $hashtagTrendings = GroupHashtagTrending::getByGroup($this->id);
            $hashtags = $hashtagTrendings->map(function ($item, $key) {
                return Hashtag::findByField('id', $item->hashtag_id);
            });
            $data['hashtag_trending'] = HashtagResource::collection($hashtags);
        }

        return $data;
    }
}
