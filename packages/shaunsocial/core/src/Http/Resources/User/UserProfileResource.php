<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Http\Resources\Hashtag\HashtagResource;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriber;
use Packages\ShaunSocial\Story\Models\Story;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageCategoryProfileResource;
use Packages\ShaunSocial\UserPage\Models\UserPageAdmin;
use Packages\ShaunSocial\Core\Http\Resources\User\PhotoVerifyItemResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingAttributeValueResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingInterestAttributeValueResource;
use Packages\ShaunSocial\Dating\Models\DatingSearchHistory;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Core\Enum\PhotoVerifyItemStatus;
use Packages\ShaunSocial\Dating\Models\DatingSwipeMatch;

class UserProfileResource extends JsonResource
{
    public function toArray($request)
    {
        $follow  = false;
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = false;

        if ($viewer) {
            $follow = $viewer->getFollow($this->id);
            $isAdmin = $viewer->isModerator();
        }
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');
        $checkPrivacy = $this->checkPrivacy($viewerId) || $isAdmin;
        $canShowFollower = $this->canViewFollower($viewerId) || $isAdmin;
        $canShowFollowing = $this->canViewFollowing($viewerId) || $isAdmin;
        $canFollow = ($viewerId != $this->id) && ($this->canFollow($viewerId) || $isAdmin);
        $canSendMessage = ($viewerId != $this->id) && ($this->canSendMessage($viewerId) || $isAdmin);
        $chatRoomId = 0;
        if ($viewerId) {
            $room = ChatRoom::getRoomTwoUser($viewerId, $this->id);
            if ($room) {
                $chatRoomId = $room->id;
            }
        }
        $canSwitch = false;

        $photosVerify = $this->getPhotoVerifyItem();
        $photosVerifyApprove =  $this->getPhotoVerifyItem(PhotoVerifyItemStatus::APPROVE->value);

        $result = [
            'id' => $this->id,
            'name' => $this->getName(),
            'user_name' => $this->user_name,
            'avatar' => $this->getAvatar(),            
            'is_followed' => $follow ? true : false,
            'enable_notify' => $follow ? $follow->enable_notify : false,
            'cover' => $this->getCover(),
            'check_privacy' =>  $checkPrivacy,
            'privacy' => $this->privacy,
            'chat_privacy' => $this->chat_privacy,
            'show_follower' => $canShowFollower,
            'show_following' => $canShowFollowing,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'zip_code' => $this->zip_code,
            'can_follow' => $canFollow,
            'can_send_message' => $canSendMessage,
            'chat_room_id' => $chatRoomId,
            'story_id' => 0,
            'is_verify' => $this->isVerify(),
	        'post_count' => $this->post_count,
            'is_page' => $this->isPage(),
            'joined_at' => $this->created_at->setTimezone($timezone)->isoFormat(config('shaun_core.time_format.date')),
            'show_paid_post' => (setting('shaun_paid_content.enable') && $this->post_paid_count > 0) ? true : false, 
            'fake_user' => $this->fake_user,
        ];

        if ($this->isPage()) {
            $isPageAdmin = false;
            if ($viewerId && $viewerId != $this->id) {
                $isPageAdmin = UserPageAdmin::getAdmin($viewerId, $this->id);
            }
            $result['is_page_admin'] = $isPageAdmin ? true : false;
        }

        if ($viewerId && $viewerId != $this->id) {
            if ($this->isPage()) {
                if ($viewer->isPage()) {
                    $isPageAdmin = UserPageAdmin::getAdmin($viewer->getParent()->id, $this->id);
                } else {
                    $isPageAdmin = UserPageAdmin::getAdmin($viewerId, $this->id);
                }

                $canSwitch = $isPageAdmin ? true : false;
            } else {
                if ($viewer->isPage()) {
                    $canSwitch = $viewer->getParent()->id == $this->id;
                }
            }
        }

        $result['canSwitch'] = $canSwitch;
        
        if ($checkPrivacy) {
            if ($this->isPage()) {
                $pageInfo = $this->getPageInfo();

                $result += [
                    'description' => $pageInfo->description,
                    'categories' => UserPageCategoryProfileResource::collection($this->getCategories()),
                    'page_hashtags' => HashtagResource::collection($this->getPageHashtags()),
                    'review_enable' => $pageInfo->review_enable,
                    'can_review' => $viewer && $viewer->canPageReview($this->id),
                    'is_page_feature' => $this->is_page_feature,
                    'websites' => $pageInfo->getWebsites(),
                    'open_hours' => $pageInfo->getOpenHours(),
                    'price' => $pageInfo->getPrice(),
                ];
                
                if ($this->checkPrivacyPageInfo('address', $viewerId)) {
                    $result['address'] = $this->location;
                }

                if ($this->checkPrivacyPageInfo('phone_number', $viewerId)) {
                    $result['phone_number'] = $pageInfo->phone_number;
                }

                if ($this->checkPrivacyPageInfo('email', $viewerId)) {
                    $result['email'] = $pageInfo->email;
                }

                if ($viewerId == $this->id) {
                    $result['page_privacy_settings'] = $this->getPrivacyPageInfoSetting();
                }

                if ($pageInfo->review_enable || $viewerId == $this->id) {
                    $result['review_score'] = formatNumber($pageInfo->review_score, 2);
                    $result['review_count'] = $pageInfo->review_count;
                }
            } else {
                $gender = $this->getGender();
               
                $result += [
                    'bio' => $this->checkPrivacyField('bio', $viewerId) ? $this->bio : '',
                    'address' => $this->checkPrivacyField('location', $viewerId) ? $this->location : '',
                    'gender' => $this->checkPrivacyField('gender_id', $viewerId) ? ($gender ? $gender->getTranslatedAttributeValue('name') : '') : '',
                ];
            }
            

            $story = Story::findByField('user_id', $this->id);
            if ($story) {
                $result['story_id'] = $story->id;
            }

            if ($canShowFollower) {
                $result += ['follower_count' => $this->follower_count];
            }

            if ($canShowFollowing) {
                $result += ['following_count' => $this->following_count];
            }
            $subscriber = UserSubscriber::getUserSubscriber($viewerId, $this->id);
            $result += [
                'canSubscriber' => $this->canSubscriber($viewerId),
                'canTip' => $this->canTip($viewerId),
                'subscriber_subscription_id' => (setting('shaun_paid_content.enable') && $subscriber) ? $subscriber->subscription_id : 0
            ];
        }

        $result['badge'] = $this->getSubscriptionBadge();
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
            'check_swipe'=> $viewer ? $viewer->checkSwipes($this->id) : false,
            'can_use_gift' => $this->canUseGift(),
            'can_view_my_gift' => $this->canViewMyGift($viewerId) || $isAdmin,
            'total_gift_received' => $this->getTotalReceived($this->id),
            'matched' => DatingSwipeMatch::checkMatched($viewerId, $this->id)
        ];

        return $result;
    }
}
