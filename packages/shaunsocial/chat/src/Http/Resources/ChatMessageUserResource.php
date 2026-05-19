<?php


namespace Packages\ShaunSocial\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Chat\Models\ChatMessageUser;
use Packages\ShaunSocial\Core\Http\Resources\User\PhotoVerifyItemResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingAttributeValueResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingInterestAttributeValueResource;
use Packages\ShaunSocial\Dating\Models\DatingSearchHistory;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Core\Enum\PhotoVerifyItemStatus;
use Packages\ShaunSocial\Dating\Models\DatingSwipeMatch;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Carbon\Carbon;

class ChatMessageUserResource extends JsonResource
{
    public function toArray($request)
    {        
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = false;
        
        $user = $this->getUser();
        $getLastMessageIdSeen = ChatMessageUser::getLastMessageIdSeen($this->room_id, $this->user_id);
        
        $photosVerify = $user->getPhotoVerifyItem();
        $photosVerifyApprove =  $user->getPhotoVerifyItem(PhotoVerifyItemStatus::APPROVE->value);

        $attributes = $user->getOriginAttributeValues();
        $interestAttributes = $user->getOriginInterestAttributeValues();
        $datingSearchHistory = DatingSearchHistory::findByField('user_id', $user->id);

        $gender = $user->getGender();
        $birthday = null;
        if ($user->birthday) {
            $birthday = new Carbon($user->birthday);
        }

        $chatRoomId = 0;
        if ($viewerId) {
            $room = ChatRoom::getRoomTwoUser($viewerId, $user->id);
            if ($room) {
                $chatRoomId = $room->id;
            }
        }

        return [
            'id' => $user->id,
            'name' => $user->getName(),
            'is_online' => $user->isOnline($viewer->id),
            'user_name' => $user->user_name,
            'avatar' => $user->getAvatar(),
            'message_seen_id'  => $getLastMessageIdSeen ? $getLastMessageIdSeen->message_id : null,
            'is_verify' => $user->isVerify(),
            'about' => $user->about,
            'company_name' => $user->company_name,
            'job_title' => $user->job_title,
            'school_name' => $user->school_name,
            'links' => $user->getLinks() ?? '',
            'attributes' => $attributes ? DatingAttributeValueResource::collection($attributes) : [],
            'interest_attributes' => $interestAttributes ? DatingInterestAttributeValueResource::collection($interestAttributes) : [],
            'age'=> $birthday ? $birthday->age : null,
            'gender' => ($gender ? $gender->getTranslatedAttributeValue('name') : ''),
            'address_full' => $user->getAddessFull(),
            'dating_addresses_id' => $user->dating_addresses_id,
            'dating_addresses_fulltext' => $user->dating_addresses_fulltext,
            'address' => $user->location,
            'dating_search_history' => json_decode($datingSearchHistory?->query, true),
            'can_view_location' => $user->canViewLocation($viewerId) || $isAdmin,
            'can_view_age' => $user->canViewAge($viewerId) || $isAdmin,
            'can_prowse_profile_privately' => $user->canBrowseProfilePrivately($viewerId),
            'blur_avatar' => $user->getAvatarBlur(),      
            'total_swipe_right'=> UserActionLog::getCount($viewerId, 'dating_swipe'),
            'check_swipe'=> $viewer->checkSwipes($user->id),
            'can_use_gift' => $user->canUseGift(),
            'matched' => DatingSwipeMatch::checkMatched($viewerId, $user->id),
            'photos_verify' => PhotoVerifyItemResource::collection($photosVerify),
            'photos_verify_approve' => PhotoVerifyItemResource::collection($photosVerifyApprove),
            'chat_room_id' => $chatRoomId,
            'can_view_my_gift' => $user->canViewMyGift($viewerId) || $isAdmin,
            'total_gift_received' => $user->getTotalReceived($user->id),
        ];
    }
}
