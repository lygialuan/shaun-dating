<?php

namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageResource;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscription;
use Packages\ShaunSocial\Core\Http\Resources\Utility\GenderResource;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Dating\Http\Resources\DatingAttributeValueResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingInterestAttributeValueResource;
use Packages\ShaunSocial\Dating\Models\DatingSearchHistory;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Dating\Support\Facades\Dating;
use Packages\ShaunSocial\Core\Enum\PhotoVerifyItemStatus;
use Packages\ShaunSocial\Core\Http\Resources\User\PhotoVerifyItemResource;

class UserMeResource extends JsonResource
{
    public function toArray($request)
    {
        $languageKey = App::getLocale();
        $language = Language::getBykey($languageKey);
        $canDelete = $this->canDelete() && setting('user.allow_delete');
        $canShowSwitchPage = $this->id ? (setting('shaun_user_page.enable') && $this->hasPermission('user_page.allow_create') ? true : false) : false;
        if ($this->isPage()) {
            $canDelete = $canDelete && $this->isPageOwner();
            $canShowSwitchPage = true;
        }
        $videoAutoPlay = $this->id ? $this->video_auto_play : true;
        $isAdmin = $this->id ? $this->isModerator() : false;
        
        $photosVerify = $this->getPhotoVerifyItem();
        $photosVerifyApprove =  $this->getPhotoVerifyItem(PhotoVerifyItemStatus::APPROVE->value);

        $result = [
            'id' => $this->id ?? 0,
            'email' => ! $this->isPage() ? $this->email : '',
            'name' => $this->getName(),
            'user_name' => $this->user_name,
            'role_id' => $this->role_id,
            'avatar' => $this->getAvatar(),
            'cover' => $this->getCover(),
            'follower_count' => $this->follower_count,
            'following_count' => $this->following_count,
            'ref_url' => $this->getRefUrl(),
            'href' => $this->getHref(),
            'already_setup_login' => $this->already_setup_login,
            'email_verified' => $isAdmin || $this->isPage() ? true : $this->email_verified,
            'phone_number' => $this->phone_number,
            'phone_verified' => $isAdmin || $this->isPage() ? true : $this->phone_verified,
            'darkmode' => $this->darkmode,
            'can_delete' => $canDelete ? true : false,
            'language' => $languageKey,
            'rtl' => $language->is_rtl ? true : false,
            'is_moderator' => $this->id ? $this->isModerator() : false,
            'video_auto_play' => $videoAutoPlay,
            'has_email' => $this->has_email,
            'timezone' => $this->id ? $this->timezone : setting('site.timezone'),
            'permissions' => $this->getPermissionsForApi(),
            'is_verify' => $this->isVerify(),
            'wallet_balance' => $this->getCurrentBalance(),
            'is_page' => $this->isPage(),
            'can_show_withdraw_wallet' => $this->canShowWithdrawWallet(),
            'can_show_send_wallet' => $this->canShowSendWallet(),
            'can_show_creator_dashboard' => $this->canShowCreatorDashBoard(),
            'can_create_post_paid_content' => $this->canCreatePostPaidContent(),
            'can_show_switch_page' => $canShowSwitchPage,
            'photos_verified' => $isAdmin || $this->isPage() ? true : $this->photos_verified,
            'photos_verify' => PhotoVerifyItemResource::collection($photosVerify),
            'photos_verify_approve' => PhotoVerifyItemResource::collection($photosVerifyApprove),
            'identity_verified' => $isAdmin || $this->isPage() ? true : $this->identity_verified,
            'fake_user' => $this->fake_user,
            'can_view_my_gift' => $this->canViewMyGift($this->id) || $isAdmin,
            'total_gift_received' => $this->getTotalReceived($this->id),
        ];

        $parent = null;
        if ($this->id) {
            if ($this->isPage()) {
                $parent = new UserPageResource($this->getParent());
                $result['page_owner'] = new UserResource($this->getOwnerPage()->getUser());
                $result['is_owner_page'] = $this->getParent()->id == $this->getOwnerPage()->getUser()->id;
                $result['is_page_feature'] = $this->is_page_feature;
            }
        }

        $result['parent'] = $parent;

        if ($result['is_moderator']){
            $result['admin_link'] = route('admin.dashboard.index');
        }

        if (setting('shaun_user_subscription.enable') && $this->id) {
            $packageName = '';
            $userSubscription = UserSubscription::getActive($this->id);
            if ($userSubscription) {
                $subscription = $userSubscription->getSubscription();
                $packageName = $subscription->package_name;
            }
            $result['membership_package_name'] = $packageName;
        }


        $gender = $this->getGender();
        $birthday = null;
        if ($this->birthday) {
            $birthday = new Carbon($this->birthday);
        }

        $attributes = $this->getOriginAttributeValues();
        $interestAttributes = $this->getOriginInterestAttributeValues();
        $datingSearchHistory = DatingSearchHistory::findByField('user_id', $this->id);
        
        $result['badge'] = $this->getSubscriptionBadge();

        $result += [
            'timezones' => getTimezoneList(),
            'age'=> $birthday ? $birthday->age : null,
            'address_full' => $this->getAddessFull(),
            'gender' => ($gender ? $gender->getTranslatedAttributeValue('name') : ''),
            'genders' => GenderResource::collection(Gender::getAll()),

            'gender_id' => $this->gender_id,
            'birthday' => $this->birthday,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'zip_code' => $this->zip_code,
            'address' => $this->location,
            'about' => $this->about,
            'company_name' => $this->company_name,
            'job_title' => $this->job_title,
            'school_name' => $this->school_name,
            'dating_addresses_fulltext' => $this->dating_addresses_fulltext,
            'dating_addresses_id' => $this->dating_addresses_id,
            'links' => $this->getLinks() ?? '',
            'attributes' => $attributes ? DatingAttributeValueResource::collection($attributes) : [],
            'interest_attributes' => $interestAttributes ? DatingInterestAttributeValueResource::collection($interestAttributes) : [],
            'dating_search_history' => json_decode($datingSearchHistory?->query, true),
            'can_view_location' => $this->canViewLocation($this->id) || $isAdmin,
            'can_view_age' => $this->canViewAge($this->id) || $isAdmin,
            'can_prowse_profile_privately' => $this->canBrowseProfilePrivately($this->id),
            'blur_avatar' => $this->getAvatarBlur(),  
            'total_swipe_right'=> UserActionLog::getCount($this->id, 'dating_swipe'),      
            'profile_completion_percent' => Dating::calculateProfileCompletion($this->resource)
        ];

        return $result;
    }
}
