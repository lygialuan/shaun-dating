<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Hashtag\HashtagResource;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageCategoryProfileResource;
use Packages\ShaunSocial\Core\Http\Resources\User\PhotoVerifyItemResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingAttributeValueResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingInterestAttributeValueResource;
use Packages\ShaunSocial\Dating\Models\DatingSearchHistory;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Core\Enum\PhotoVerifyItemStatus;
use Packages\ShaunSocial\Dating\Models\DatingSwipeMatch;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Carbon\Carbon;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $followed = false;
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = false;
        
        if ($viewer) {
            $followed = $viewer->getFollow($this->id) ? true : false;
            $isAdmin = $viewer->isModerator();
        }

        $canShowFollower = $this->canViewFollower($viewerId) || $isAdmin;
        $canFollow = $this->canFollow($viewerId) || ($isAdmin && $viewerId != $this->id);

        $result = [
            'id' => $this->id,
            'name' => $this->getName(),
            'user_name' => $this->user_name,
            'cover' => $this->getCover(),
            'avatar' => $this->getAvatar(),
            'bio' => $this->bio,
            'is_followed' => $followed,
            'show_follower' => $canShowFollower,
            'can_follow' => $canFollow,
            'href' => $this->getHref(),
            'is_verify' => $this->isVerify(),
            'is_page' => $this->isPage(),
        ];

        if ($this->isPage()) {
            $result += [
                'categories' => UserPageCategoryProfileResource::collection($this->getCategories()),
                'page_hashtags' => HashtagResource::collection($this->getPageHashtags()),
                'is_page_feature' => $this->is_page_feature
            ];
        } 

        if ($canShowFollower) {
            $result += ['follower_count' => $this->follower_count];
        }

        $result['badge'] = $this->getSubscriptionBadge();

        $photosVerify = $this->getPhotoVerifyItem();
        $photosVerifyApprove =  $this->getPhotoVerifyItem(PhotoVerifyItemStatus::APPROVE->value);
        $result['photos_verify'] = PhotoVerifyItemResource::collection($photosVerify);
        $result['photos_verify_approve'] = PhotoVerifyItemResource::collection($photosVerifyApprove);

        $attributes = $this->getOriginAttributeValues();
        $interestAttributes = $this->getOriginInterestAttributeValues();
        $datingSearchHistory = DatingSearchHistory::findByField('user_id', $this->id);

        $gender = $this->getGender();
        $birthday = null;
        if ($this->birthday) {
            $birthday = new Carbon($this->birthday);
        }

        $chatRoomId = 0;
        if ($viewerId) {
            $room = ChatRoom::getRoomTwoUser($viewerId, $this->id);
            if ($room) {
                $chatRoomId = $room->id;
            }
        }
        $result += [
            'about' => $this->about,
            'company_name' => $this->company_name,
            'job_title' => $this->job_title,
            'school_name' => $this->school_name,
            'links' => $this->getLinks() ?? '',
            'attributes' => $attributes ? DatingAttributeValueResource::collection($attributes) : [],
            'interest_attributes' => $interestAttributes ? DatingInterestAttributeValueResource::collection($interestAttributes) : [],
            'age'=> $birthday ? $birthday->age : null,
            'gender' => ($gender ? $gender->getTranslatedAttributeValue('name') : ''),
            'address_full' => $this->getAddessFull(),
            'dating_addresses_id' => $this->dating_addresses_id,
            'dating_addresses_fulltext' => $this->dating_addresses_fulltext,
            'address' => $this->location,
            'dating_search_history' => json_decode($datingSearchHistory?->query, true),
            'can_view_location' => $this->canViewLocation($viewerId) || $isAdmin,
            'can_view_age' => $this->canViewAge($viewerId) || $isAdmin,
            'can_prowse_profile_privately' => $this->canBrowseProfilePrivately($viewerId),
            'blur_avatar' => $this->getAvatarBlur(),      
            'total_swipe_right'=> UserActionLog::getCount($viewerId, 'dating_swipe'),
            'check_swipe'=> $viewer->checkSwipes($this->id),
            'can_use_gift' => $this->canUseGift(),
            'matched' => DatingSwipeMatch::checkMatched($viewerId, $this->id),
            'chat_room_id' => $chatRoomId,
            'can_view_my_gift' => $this->canViewMyGift($viewerId) || $isAdmin,
            'total_gift_received' => $this->getTotalReceived($this->id),
        ];
        
        return $result;
    }
}
