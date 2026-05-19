<?php

namespace Packages\ShaunSocial\Core\Models;

use App\Models\User as UserCore;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Chat\Models\ChatRoomMember;
use Packages\ShaunSocial\Core\Traits\HasAvatar;
use Packages\ShaunSocial\Core\Traits\HasAvatarBlur;
use Packages\ShaunSocial\Core\Traits\HasCover;
use Packages\ShaunSocial\Core\Traits\HasReport;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatus;
use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Core\Support\Facades\Mail;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasLink;
use Packages\ShaunSocial\Core\Traits\IsSubjectNotification;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriber;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriberPackage;
use Packages\ShaunSocial\Story\Models\Story;
use Packages\ShaunSocial\UserPage\Models\UserPageAdmin;
use Packages\ShaunSocial\UserPage\Models\UserPageCategory;
use Packages\ShaunSocial\UserPage\Models\UserPageFollowReportUpdate;
use Packages\ShaunSocial\UserPage\Models\UserPageInfo;
use Packages\ShaunSocial\UserPage\Models\UserPageReview;
use Packages\ShaunSocial\UserPage\Models\UserPageStatistic;
use Packages\ShaunSocial\UserPage\Models\UserPageToken;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscription;
use Packages\ShaunSocial\UserVerify\Models\UserVerifyFile;
use Packages\ShaunSocial\UserVerify\Notification\UserVerifyRequestUnverifyNotification;
use Packages\ShaunSocial\UserVerify\Notification\UserVerifyRequestVerifyNotification;
use Packages\ShaunSocial\Wallet\Models\WalletTransaction;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Dating\Models\DatingAttributeValue;
use Packages\ShaunSocial\Dating\Models\DatingInterestAttributeValue;
use Packages\ShaunSocial\Dating\Models\DatingAddress;
use Packages\ShaunSocial\Dating\Models\DatingSwipe;
use Packages\ShaunSocial\Core\Models\PhotoVerifyItem;
use Packages\ShaunSocial\UserPage\Models\UserPageCreateSubProfileFakePhoto as FakePhoto;
use Packages\ShaunSocial\Gift\Models\GiftAggregate;
use Packages\ShaunSocial\AiChatProfiles\Models\AiPersonaConfig;

class User extends UserCore
{
    use HasCacheQueryFields, IsSubject, HasStorageFiles, HasCover, HasReport, HasAvatar, IsSubjectNotification, HasLink, HasCachePagination, HasAvatarBlur;

    protected $cacheQueryFields = [
        'id',
        'user_name',
        'email',
        'ref_code'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'user_name',
        'follower_count',
        'following_count',
        'block_count',
        'notify_count',
        'enable_notify',
        'hashtag_follow_count',
        'bio',
        'about',
        'location',
        'gender_id',
        'notify_setting',
        'hashtags',
        'email_verified',
        'privacy',
        'privacy_setting',
        'ref_code',
        'already_setup_login',
        'daily_email_enable',
        'email_setting',
        'darkmode',
        'chat_count',
        'timezone',
        'chat_privacy',
        'is_active',
        'has_active',
        'links',
        'ip',
        'birthday',
        'language',
        'video_auto_play',
        'has_email',
        'verify_status',
        'verify_status_at',
        'is_page',
        'categories',
        'page_info_privacy',
        'page_hashtags',
        'is_page_feature',
        'page_feature_view',
        'wallet_notify_lower',
        'country_id',
        'state_id',
        'city_id',
        'zip_code',
        'user_subscription_has_trial',
        'post_count',
		'group_count',
        'phone_number',
        'phone_verified',
		'field_privacy',
        'earn_amount',
        'earn_fee_amount',
        'paid_content_trial_day',
        'post_paid_count',
        'subscriber_count',
        'withdrawal_info',
        'photos_verified',
        'identity_verified',
        'school_name',
        'job_title',
        'company_name',
        'attributes',
        'interest_attributes',
        'dating_addresses_fulltext',
        'dating_addresses_id',
        'blur_avatar_file_id',
        'has_reviewed_photos',
        'photos_uploaded_at',
        'fake_user'
    ];

    protected $casts = [
        'verify_status' => UserVerifyStatus::class,
        'verify_status_at' => 'datetime',
        'email_verified' => 'boolean',
        'enable_notify' => 'boolean',
        'already_setup_login' => 'boolean',
        'daily_email_enable' => 'boolean',
        'is_active' => 'boolean',
        'has_active' => 'boolean',
        'video_auto_play' => 'boolean',
        'has_email' => 'boolean',
        'is_page' => 'boolean',
        'is_page_feature' => 'boolean',
        'wallet_notify_lower' => 'boolean',
        'phone_verified' => 'boolean',
        'photos_verified' => 'boolean',
        'identity_verified' => 'boolean',
        'has_reviewed_photos' => 'boolean',
        'photos_uploaded_at' => 'datetime',
        'fake_user' => 'boolean',
    ];

    protected $storageFields = [
        'cover_file_id'
    ];

    protected $postNotifyDefault = [
        'comment' => true,
        'reply' => true,
        'like' => true,
        'mention' => true,
        'share' => true
    ];

    
    protected $systemNotifyDefault = [
        'invite' => true
    ];

    protected $followNotifyDefault = [
        'new_follow' => true
    ];

    protected $chatNotifyDefault = [
        'chat_request' => true
    ];
    
    protected $storyNotifyDefault = [
        'story_end' => true
    ];

    protected $paidContentNotifyDefault = [
        'new_subscriber' => true,
        'new_ppv_purchase' => true,
    ];

    protected $privacySettingDefault = [
        'browse_profile_privately' => true,
        'show_my_location' => true,
        'show_my_age' => true,
        'hide_my_account' => false,
        'show_my_gift' => true,
    ];

    protected $privacyPageInfoDefault = [
        'address' => 3,
        'phone_number' => 3,
        'email' => 3
    ];

    protected $privacyFieldDefault = [
        'bio' => 1,
        'about' => 1,
        'location' => 1,
        'birthday' => 1,
        'link' => 1,
        'gender_id' => 1
    ];

    protected $parent = null;

    public function getAvatarDefault()
    {
        return setting('feature.avatar_default');
    }

    public function getCoverDefault()
    {
        return setting('feature.cover_default');
    }

    public function getRole()
    {
        return Role::findByField('id', $this->role_id);
    }

    public function isModerator()
    {
        return $this->getRole()->isSuperAdmin() || $this->getRole()->isModerator();
    }

    public function isSuperAdmin()
    {
        return $this->getRole()->isSuperAdmin();
    }

    public function getPermissionValues()
    {
        return $this->getRole()->getPermissionValues();
    }

    public function hasPermission($permission)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = Permission::getPermissionsForUser();
        $check = $permissions->first(function ($value, $key) use ($permission) {
            return $value->key == $permission;
        });
        if ($check) {
            if ($this->isModerator()) {
                return true;
            }
        }
        
        $permissions = $this->getPermissionValues();
        
        return ! empty($permissions[$permission]);
    }

    public function getPermissionValue($permission, $default = '')
    {
        $permissions = $this->getPermissionValues();

        return isset($permissions[$permission]) ? $permissions[$permission] : $default;
    }

    public function getFollow($userId)
    {
        return UserFollow::getFollow($this->id, $userId);
    }

    public function getPageReview($userPageId)
    {
        return UserPageReview::getReview($this->id, $userPageId);
    }

    public function canReport($userId)
    {
        return $this->canView($userId);
    }

    public function canPageReview($userPageId)
    {
        return ($this->id != $userPageId && ! $this->getPageReview($userPageId));
    }

    public function getHashtagFollow($hashtag)
    {
        return HashtagFollow::getFollow($this->id, $hashtag);
    }

    public function getHastagRelative()
    {
        $hashtagArray = [];
        if ($this->hashtag_follow_count) {
            $hashtagArray = $this->getHashtagFollows()->pluck('hashtag_id')->toArray();
        }

        $hashtags = UserHashtagSuggest::getHashtagForUser($this->id);

        return array_merge($hashtagArray, $hashtags->pluck('hashtag_id')->toArray());
    }

    public function addHashtagFollow($hashtag)
    {
        HashtagFollow::create([
            'user_id' => $this->id,
            'name' => $hashtag,
        ]);
    }

    public function addFollow($userId, $isPage = false)
    {
        UserFollow::create([
            'user_id' => $this->id,
            'user_is_page' => $this->isPage(),
            'follower_id' => $userId,
            'follower_is_page' => $isPage
        ]);
    }

    public function getName()
    {
        return $this->name;
    }

    public function canViewFollower($viewerId)
    {
        if (!$this->canView($viewerId)) {
            return false;
        }

        return $this->checkPrivacySetting('show_follower', $viewerId);
    }

    public function canViewProfilePost($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function canViewFollowing($viewerId)
    {
        if (!$this->canView($viewerId)) {
            return false;
        }

        return $this->checkPrivacySetting('show_following', $viewerId);
    }

    public function canFollow($viewerId)
    {
        return $viewerId != $this->id && $this->is_active;
    }

    public function canSendMessage($viewerId)
    {
        if (! $viewerId || $viewerId == $this->id) {
            return false;
        }
        
        switch ($this->chat_privacy)
        {
            case config('shaun_chat.privacy.everyone'):
                return true;
                break;
            case config('shaun_chat.privacy.my_follower'):
                return UserFollow::getFollow($viewerId, $this->id);
                break;
        }
        
        return false;
    }

    public function canViewProfile($viewerId)
    {
        return $this->canView($viewerId, false);
    }

    public function canView($viewerId, $checkPrivacy = true) 
    {
        //check active
        if (! $this->is_active) {
            return false;
        }

        if ($checkPrivacy && !$this->checkPrivacy($viewerId)) {
            return false;
        }

        if ($this->checkBlock($viewerId)) {
            return false;
        }

        return true;
    }

    public function checkPrivacy($viewerId)
    {
        return $this->checkPrivacyByValue($this->privacy, $viewerId);
    }

    public function checkPrivacyByValue($value, $viewerId)
    {
        if ($viewerId == $this->id) {
            return true;
        }

        switch ($value)
        {
            case config('shaun_core.privacy.user.everyone'):
                return true;
                break;
            case config('shaun_core.privacy.user.my_follower'):
                if ($viewerId) {
                    return UserFollow::getFollow($viewerId, $this->id);
                }                
                break;
        }
        
        return false;
    }

    public function getFollows()
    {
        return UserFollow::findByField('user_id',$this->id, true);
    }

    public function getHashtagFollows()
    {
        return HashtagFollow::findByField('user_id',$this->id, true);
    }

    public function checkBlock($userId)
    {
        if (!$userId) {
            return false;
        }
        
        if ($this->block_count > config('shaun_core.block.max_number_query_each_user')) {
            return UserBlock::checkBlock($this->id, $userId);
        } else {
            return in_array($userId, UserBlock::getBlocks($this->id));
        }
    }

    public function getBlock($userId)
    {
        return UserBlock::getBlock($this->id, $userId);
    }

    public function addBlock($userId, $isPage = false)
    {
        UserBlock::create([
            'user_id' => $this->id,
            'blocker_id' => $userId,
            'is_page' => $isPage
        ]);
    }

    public static function getResourceClass()
    {
        return UserResource::class;
    }

    public static function getPrivacyList()
    {
        return [
            config('shaun_core.privacy.user.everyone') => __('Everyone'),
            config('shaun_core.privacy.user.my_follower') => __('My followers'),
            config('shaun_core.privacy.user.only_me') => __('Only me'),
        ];
    }

    public static function getChatPrivacyList()
    {
        return [
            config('shaun_chat.privacy.everyone') => __('Everyone'),
            config('shaun_chat.privacy.my_follower') => __('My followers'),
            config('shaun_chat.privacy.no_one') => __('No one'),
        ];
    }

    public function getPrivacyPageInfoSetting()
    {
        $result = [];
        if ($this->page_info_privacy) {
            $result = json_decode($this->page_info_privacy, true);
            if (! $result) {
                $result = [];
            }
        }
        return array_merge($this->privacyPageInfoDefault, $result);
    }

    public function checkPrivacyPageInfo($name, $viewerId)
    {
        $settings = $this->getPrivacyPageInfoSetting();
        $value = isset($settings[$name]) ? $settings[$name] : 1;

        return $this->checkPrivacyByValue($value, $viewerId);
    }

    public function getPrivacyFieldSetting()
    {
        $result = [];
        if ($this->field_privacy) {
            $result = json_decode($this->field_privacy, true);
            if (! $result) {
                $result = [];
            }
        }
        return array_merge($this->privacyFieldDefault, $result);
    }

    public function checkPrivacyField($name, $viewerId)
    {
        $settings = $this->getPrivacyFieldSetting();
        $value = isset($settings[$name]) ? $settings[$name] : 1;

        return $this->checkPrivacyByValue($value, $viewerId);
    }

    public function getNotifySetting()
    {
        $result = [];
        if ($this->notify_setting) {
            $result = json_decode($this->notify_setting, true);
            if (! $result) {
                $result = [];
            }
        }
        return array_merge($this->postNotifyDefault, $this->systemNotifyDefault, $this->followNotifyDefault, $this->chatNotifyDefault, $this->storyNotifyDefault, $this->paidContentNotifyDefault, $result);
    }

    public function getPostNotifySetting()
    {
        return $this->getNotifySettingByGroup($this->postNotifyDefault);
    }

    public function getSystemNotifySetting()
    {
        return $this->getNotifySettingByGroup($this->systemNotifyDefault);
    }

    public function getFollowNotifySetting()
    {
        return $this->getNotifySettingByGroup($this->followNotifyDefault);
    }

    public function getChatNotifySetting()
    {
        return $this->getNotifySettingByGroup($this->chatNotifyDefault);
    }

    public function getStoryNotifySetting()
    {
        return $this->getNotifySettingByGroup($this->storyNotifyDefault);
    }

    public function getPaidContentNotifySetting()
    {
        return $this->getNotifySettingByGroup($this->paidContentNotifyDefault);
    }

    public function getNotifySettingByGroup($group)
    {
        $notifySetting = $this->getNotifySetting();
        
        $result = collect($group)->map(function($value, $key) use ($notifySetting){
            return $notifySetting[$key];
        })->toArray();

        return $result;
    }

    public function checkNotifySetting($name)
    {
        $notifySetting = $this->getNotifySetting();

        return isset($notifySetting[$name]) ? $notifySetting[$name] : false;
    }

    public function getPrivacySetting()
    {
        $result = [];
        if ($this->privacy_setting) {
            $result = json_decode($this->privacy_setting, true);
            if (! $result) {
                $result = [];
            }
        }
        return array_merge($this->privacySettingDefault, $result);
    }

    public function checkPrivacySetting($name, $viewerId)
    {
        $privacySetting = $this->getPrivacySetting();

        return ($viewerId == $this->id) || (isset($privacySetting[$name]) ? $privacySetting[$name] : false);
    }

    public function checkUserEnableFollowNotification()
    {
        if ($this->privacy == config('shaun_core.privacy.user.only_me')) {
            return false;
        }
        
        return Cache::remember('check_user_enable_follow_notification_'.$this->id, config('shaun_core.cache.time.short'), function () {            
            return UserFollow::where('follower_id', $this->id)->where('enable_notify', true)->first();
        });
    }

    public function getEmailSetting()
    {
        return [];
    }

    public function getLinks()
    {
       return $this->getLinkWithField('links');
    }

    public function getRefUrl()
    {
        return route('web.user.signup').'?ref_code='.$this->ref_code;
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function getHref()
    {
        if ($this->id) {
            return route('web.user.profile',[
                'user_name' => $this->user_name
            ]);
        }
        
        return  '';
    }

    public function getMailUnsubscribe()
    {
        return MailUnsubscribe::getByEmail($this->email);
    }

    public function isOnline($viewerId = 0)
    {
        if ($this->id && $viewerId == $this->id) {
            return true;
        }

        return Cache::has('user_online_'.$this->id);
    }

    public function setOnline()
    {
        Cache::put('user_online_'.$this->id, true, config('shaun_core.cache.time.user_online'));
    }

    public function getHashtags()
    {
        $hashtags = '';
        if ($this->hashtags) {
            $collection = Str::of($this->hashtags)->explode(' ');
            $hashtags = $collection->map(function ($value, $key) {
                return Hashtag::findByField('id', $value);
            })->pluck('name')->join(' ');
        }

        return $hashtags;
    }

    public function getGender()
    {
        if ($this->gender_id) {
            return Gender::findByField('id', $this->gender_id);
        }

        return null;
    }

    public function isRoot()
    {
        return $this->id == config('shaun_core.core.user_root_id');
    }

    public function canDelete()
    {
        return ! $this->isRoot();
    }

    public function canActionOnAdminPanel($viewer)
    {
        if ($viewer->isRoot()) {
            return true;
        }

        if ($this->isSuperAdmin()) {
            return false;
        }

        if ($this->isModerator() && ! $viewer->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    public function getReportToUserId($userId = null)
    {
        return $this->id;
    }

    public function getFcmTokens()
    {
        return UserFcmToken::findByField('user_id', $this->id, true);
    }

    public function deleteFcmToken($fcmToken)
    {
        $fcmTokens = $this->getFcmTokens();
        $token = $fcmTokens->first(function ($value, $key) use ($fcmToken) {
            return $value->token == $fcmToken;
        });

        if ($token) {
            $token->delete();
        }
    }

    public function deleteFcmTokens($fcmToken = null)
    {
        $fcmTokens = $this->getFcmTokens();
        $fcmTokens->each(function($token) use ($fcmToken) {
            if ($token->token != $fcmToken) {
                $token->delete();
            }
        });
    }

    public function getPermissionsForApi()
    {
        $permissions = Permission::getPermissionsForUser();
        $results = [];
        foreach ($permissions as $permission) {
            switch ($permission->type) {
                case 'checkbox':
                    $results[$permission->key] = $this->hasPermission($permission->key);
                    break;
                case 'text':
                    $results[$permission->key] = $this->getPermissionValue($permission->key);
                    break;
                case 'number':
                    $results[$permission->key] = $this->getPermissionValue($permission->key, 0);
                    break;
            }
        }
        return $results;
    }
    
    public function isVerify()
    {
        return setting('user_verify.enable') && $this->verify_status == UserVerifyStatus::OK;
    }

    public function getVerifyFiles()
    {
        return UserVerifyFile::getFilesByUserId($this->id);
    }

    public function getCurrentBalance()
    {
        return WalletTransaction::getBalanceByUser($this->id);
    }

    public function isPage()
    {
        return $this->is_page;
    }

    public function isPageFeature()
    {
        return $this->is_page_feature;
    }

    public function getAdminPage($userId)
    {
        if (! $this->isPage()) {
            return false;
        }

        return UserPageAdmin::getAdmin($userId, $this->id);
    }

    public function getOwnerPage()
    {
        if (! $this->isPage()) {
            return null;
        }

        return UserPageAdmin::getOwner($this->id);
    }

    public function getPageAdminCurrentlyLogin()
    {
        return UserPageAdmin::getAdmin($this->getParent()->id, $this->id);
    }

    public function isPageOwner()
    {
        if (! $this->isPage()) {
            return false;
        }

        $parent = $this->getParent();
        if (! $parent) {
            return false;
        }

        $admin = $this->getAdminPage($parent->id);
        if (! $admin) {
            return false;
        }

        return $admin->isPageOwner();
    }

    public function getPages()
    {
        if (! $this->isPage()) {
            $pages = UserPageAdmin::findByField('user_id', $this->id, true);
            return $pages->map(function ($item, $key) {
                return User::findByField('id', $item->user_id);
            });
        }

        return [];
    }

    public function getParent()
    {
        if (! $this->parent) {
            if ($this->isPage() && $this->currentAccessToken()) {
                $result = UserPageToken::findByField('token', $this->currentAccessToken()->token);
                if ($result) {
                    $this->parent = User::findByField('id', $result->user_id);
                }
            }
        }

        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getPageInfo()
    {
        return UserPageInfo::findByField('user_page_id', $this->id);
    }

    public function getCategories()
    {
        if ($this->categories) {
            $collection = Str::of($this->categories)->explode(' ');
            return $collection->map(function ($value, $key) {
                $category = UserPageCategory::findByField('id', $value);
                if (! $category->canUse()) {
                    return null;
                } else {
                    return $category;
                }
            })->filter(function ($value, $key) {
                return $value;
            });
        }

        return [];
    }

    public function getPageHashtags()
    {
        $hashtags = collect();
        if ($this->page_hashtags) {
            $collection = Str::of($this->page_hashtags)->explode(' ');
            $hashtags = $collection->map(function ($value, $key) {
                return Hashtag::findByField('id', $value);
            });
        }

        return $hashtags;
    }

    public function getAge()
    {
        if ($this->birthday) {
            return Carbon::parse(date('Y-01-01',strtotime($this->birthday)))->age;
        }

        return null;
    }

    public function getBirthdayYear()
    {
        if ($this->birthday) {
            return Carbon::parse($this->birthday)->format('Y');
        }

        return 0;
    }

    public function addPageStatistic($type, $viewer, $subject = null ,$hasLock = true)
    {
        if ($this->isPage()) {
            UserPageStatistic::add($this->id, $type, $viewer, $subject, $hasLock);
        }
    }

    public function doVerify()
    {
        $this->update([
            'verify_status' => UserVerifyStatus::OK,
            'verify_status_at' => now(),
            'identity_verified' => true
        ]);

        UserVerifyFile::deleteByUser($this);

        Notification::send($this, $this, UserVerifyRequestVerifyNotification::class, null, ['is_system' => true], 'shaun_user_verify', false);
    }

    public function doRejectVerify($user, $reason = '')
    {
        $this->update([
            'verify_status' => UserVerifyStatus::NONE,
            'verify_status_at' => null,
        ]);

        UserVerifyFile::deleteByUser($this);

        //Notification::send($this, $user, UserVerifyRequestRejectNotification::class, $user, [], 'shaun_user_verify', false);
        if ($this->isPage()) {
            Mail::send('user_verify_reject', $this->getOwnerPage()->getUser(), [
                'reason' => $reason,
                'link' => $this->getHref()
            ]);
        } else {
            Mail::send('user_verify_reject', $this, [
                'reason' => $reason,
                'link' => $this->getHref()
            ]);
        }
        
    }

    public function doUnVerify($isNotify = true)
    {
        $this->update([
            'verify_status' => UserVerifyStatus::NONE,
            'verify_status_at' => null,
        ]);

        UserVerifyFile::deleteByUser($this);

        if ($isNotify) {
        Notification::send($this, $this, UserVerifyRequestUnverifyNotification::class, null, ['is_system' => true], 'shaun_user_verify', false);
    }

    }

    public function doRemoveLoginAllDevices()
    {
        $tokens = $this->tokens();
        $tokens->each(function($token) {
            $token->delete();
        });
        $this->deleteFcmTokens();
    }

    public function canShowWithdrawWallet()
    {
        return setting('shaun_wallet.enable') && checkEnableFundTransfer() && (! $this->isPage() || ($this->isPage() && $this->isPageOwner()));
    }

    public function canShowSendWallet()
    {
        return setting('shaun_wallet.enable') && (! $this->isPage() || ($this->isPage() && $this->isPageOwner()));
    }

    public function getAddessFull()
    {
        $address = $this->location ? $this->location : '';

        if ($this->city_id) {
            $city = City::findByField('id', $this->city_id);
            if ($city) {
                $address .= $address ? ', '.$city->getTranslatedAttributeValue('name') : $city->getTranslatedAttributeValue('name');
            }
        }

        if ($this->state_id) {
            $state = State::findByField('id', $this->state_id);
            if ($state) {
                $address .= $address ? ', '.$state->getTranslatedAttributeValue('name') : $state->getTranslatedAttributeValue('name');
            }
        }

        if ($this->zip_code) {
            $address .= $address ? ', '.$this->zip_code : $this->zip_code;
        }

        if ($this->country_id) {
            $country = Country::findByField('id', $this->country_id);
            if ($country) {
                $address .= $address ? ', '.$country->getTranslatedAttributeValue('name') : $country->getTranslatedAttributeValue('name');
            }
        }

        return $address;
    }

    public function stopCurrentUserSubscription()
    {
        $userSubscription = UserSubscription::getActive($this->id);
        if ($userSubscription) {
            $userSubscription->getSubscription()->doStop(true);
        }
    }

    public function getSubscriptionBadge()
    {
        if (setting('shaun_user_subscription.enable') && $this->id) {
            $userSubscription = UserSubscription::getActive($this->id);

            if($userSubscription){
                $userSubscriptionPlan = $userSubscription->getPlan();
                $userSubscriptionPackage = $userSubscriptionPlan->getPackage();
                if ($userSubscriptionPackage->is_show_badge) {
                    return [
                        'badge_name' => $userSubscriptionPackage->badge_name ? $userSubscriptionPackage->badge_name : $userSubscriptionPackage->name,
                        'background_color' => $userSubscriptionPackage->badge_background_color,
                        'border_color' => $userSubscriptionPackage->badge_border_color,
                        'text_color' => $userSubscriptionPackage->badge_text_color,
                    ];
                }
            }
        }

        return null;
    }

    public function canCreatePostPaidContent()
    {
        if (! $this->canShowCreatorDashBoard()) {
            return false;
        }

        if (! $this->checkProfileForPaidContent()) {
            return false;
        }

        if (! $this->checkVerifyForPaidContent()) {
            return false;
        }

        if (! $this->checkSubscriptionForPaidContent()) {
            return false;
        }

        if (! $this->checkPriceForPaidContent()) {
            return false;
        }

        return true;
    }

    public function checkProfileForPaidContent()
    {
        return $this->avatar_file_id && $this->cover_file_id;
    }

    public function checkVerifyForPaidContent()
    {
       return ! setting('user_verify.enable') || ! setting('shaun_paid_content.require_verify') || $this->verify_status == UserVerifyStatus::OK;
    }

    public function checkSubscriptionForPaidContent()
    {
        return ($this->isModerator() || $this->hasPermission('paid_content.allow_create'));
    }

    public function checkPriceForPaidContent()
    {
        return count(UserSubscriberPackage::findByField('user_id', $this->id, true));
    }

    public function canShowCreatePostPaidContent()
    {
        return $this->canShowCreatorDashBoard();
    }

    public function canTip($userId)
    {
        if (! $userId) {
            return false;
        }

        if ($userId == $this->id) {
            return false;
        }
        
        if (! $this->canView($userId)) {
            return false;
        }

        if (! $this->canCreatePostPaidContent()) {
            return false;
        }
        
        return true;
    }

    public function canSubscriber($userId)
    {
        if (! $userId) {
            return false;
        }

        if ($userId == $this->id) {
            return false;
        }
        
        if (! $this->canView($userId)) {
            return false;
        }

        if (! $this->canCreatePostPaidContent()) {
            return false;
        }

        return ! UserSubscriber::getUserSubscriber($userId, $this->id);
    }

    public function canShowCreatorDashBoard()
    {
        if (! setting('shaun_paid_content.enable')) {
            return false;
        }

        if (! $this->isPage() && ! setting('shaun_paid_content.user_create')) {
            return false;
        }

        if ($this->isPage() && ! setting('shaun_paid_content.page_create')) {
            return false;
        }

        return true;
    }

    public function getTwoFactor()
    {
        return UserTwoFactor::getByUser($this->id, true);
    }

    public static function checkExistPhoneNumber($phoneNumber)
    {
        return Cache::remember(self::getCacheKeyExistPhoneNumber($phoneNumber), config('shaun_core.cache.time.model_query'), function () use ($phoneNumber) {
            $user = self::where('phone_number', $phoneNumber)->where('phone_verified', true)->first();

            return is_null($user) ? false : $user;
        });
    }

    public static function getCacheKeyExistPhoneNumber($phoneNumber)
    {
        return 'user_phone_number_'.$phoneNumber;
    }

    public function getWithdrawalInfo()
    {
        $result = [
            'bank_detail' => '', 
            'paypal_email' => ''
        ];

        if ($this->withdrawal_info) {
            $result = array_merge($result, json_decode($this->withdrawal_info, true));
        }

        return $result;
    }

    public function getOriginAttributeValues()
    {
        if (!$this->getAttributeValue('attributes')) {
            return collect([]);
        }
        $collection = Str::of($this->getAttributeValue('attributes'))->explode(' ');
        return $collection
            ->map(function ($value) {
                return DatingAttributeValue::findByField('id', $value);
            })
            ->filter(function ($item) {
                return $item && $item->is_active;
            });
    }

    public function getOriginInterestAttributeValues()
    {
        if (!$this->getAttributeValue('interest_attributes')) {
            return collect([]);
        }
        $collection = Str::of($this->getAttributeValue('interest_attributes'))->explode(' ');
        return $collection
            ->map(function ($value) {
                return DatingInterestAttributeValue::findByField('id', $value);
            })
            ->filter(function ($item) {
                return $item && $item->is_active;
            });
    }

    protected function scopeAttribute($builder, $attributes)
    {
        $builder->where(function ($query) use ($attributes) {
            foreach ($attributes as $group) {
                $query->where(function ($subQuery) use ($group) {
                    foreach ($group as $tagId) {
                        $subQuery->orWhereFullText('attributes', $tagId);
                    }
                });
            }
        });
    }

    protected function scopeInterestAttributes($builder, $attributes)
    {
        $builder->where(function ($query) use ($attributes) {
            foreach ($attributes as $group) {
                $query->where(function ($subQuery) use ($group) {
                    foreach ($group as $tagId) {
                        $subQuery->orWhereFullText('interest_attributes', $tagId);
                    }
                });
            }
        });
    }

    public function getAddress()
    {
        return DatingAddress::findByField('id', $this->dating_address_id);
    }

    public function checkSwipes($userId)
    {
        if (!$userId) {
            return false;
        }
        return DatingSwipe::checkSwipes($this->id, $userId);
    }

    public function canViewLocation($viewerId)
    {
        if (!$this->canView($viewerId)) {
            return false;
        }

        return $this->checkPrivacySetting('show_my_location', $viewerId);
    }

    public function canViewAge($viewerId)
    {
        if (!$this->canView($viewerId)) {
            return false;
        }

        return $this->checkPrivacySetting('show_my_age', $viewerId);
    }

    public function canBrowseProfilePrivately($viewerId)
    {
        if (!$this->canView($viewerId)) {
            return false;
        }

        return $this->checkPrivacySetting('browse_profile_privately', $viewerId);
    }
    
    public function getPhotoVerifyItem($status = null) {
        return PhotoVerifyItem::getPhotosVerify($this->id, $status);
    }

    public function photoVerifyItems()
    {
        return $this->hasMany(PhotoVerifyItem::class, 'user_id');
    }

    public function canUseGift()
    {
        if(!setting('shaun_gift.enable')){
            return false;
        }
        
        if(!$this->hasPermission('gift.allow_gift_receiving')){
            return false;
        }

        return true;
    }

    public function getFakePhotoByUserId()
    {
        $photo = FakePhoto::findByField('user_id', $this->id);

        return $photo ? "$photo->gender/$photo->photo" : null;
    }

    public function hideMyAccount($viewerId)
    {
        return $this->checkPrivacySetting('hide_my_account', $viewerId);
    }

    public function canViewMyGift($viewerId)
    {
        if(!setting('shaun_gift.enable')){
            return false;
        }

        if($viewerId == $this->id){ 
            return true;
        }

        if (!$this->canView($viewerId)) {
            return false;
        }

        return $this->checkPrivacySetting('show_my_gift', $viewerId);
    }

    public function getTotalReceived(){
        return GiftAggregate::getTotalReceived($this->id);
    }

    public function getAIPersonaConfig(){
        return AiPersonaConfig::findByField('profile_id', $this->id);
    }
    

    public static function booted()
    {
        static::creating(function ($user) {
            if ($user->is_active === null) {
                $user->is_active = ! setting('feature.enable_must_active');
            }

            if ($user->timezone === null) {
                $user->timezone = setting('site.timezone');
            }
            
            if ($user->email_verified === null) {
                $user->email_verified = ! setting('feature.email_verify');
            }

            if ($user->phone_verified === null) {
                $user->phone_verified = ! setting('feature.phone_verify');
            }

            if ($user->photos_verified === null) {
                $user->photos_verified = ! setting('feature.photos_verify');
            }

            if ($user->identity_verified === null) {
                $user->identity_verified = ! setting('feature.identity_verify');
            }
            
            if ($user->has_email === null) {
                $user->has_email = true;
            }

            if ($user->is_page === null) {
                $user->is_page = false;
            }
            
            $user->ref_code = uniqid();            
            $user->ip = request()->ip();
            $user->language = App::getLocale();
        
            if (! $user->role_id) {
                $roleDefault = Role::getDefault();
                $user->role_id = $roleDefault->id;
            }

            if ($user->darkmode === null) {
                $user->darkmode = setting('site.appearance_mode_default');
            }

            if ($user->already_setup_login === null) {
                $user->already_setup_login = ! setting('feature.allow_follow_hashtags_and_users_when_signup');
            }

            if ($user->video_auto_play === null) {
                $user->video_auto_play = setting('site.autoplay_video_default');
            }
        });

        static::created(function ($user) {
            if (setting('feature.auto_follow')) {
                $userIds = collect(explode(',', setting('feature.auto_follow')));
                $users = $userIds->map(function ($item, int $key) {
                    if ($item) {
                        return self::findByField('id', $item);
                    } else {
                        return false;
                    }
                })->filter(function ($item) {
                    return $item;
                });

                $users->each(function($item) use ($user){
                    $user->addFollow($item->id, $item->isPage());
                });
            }
            
            if ($user->phone_number) {
                Cache::forget(self::getCacheKeyExistPhoneNumber($user->phone_number));
            }
            
        });

        static::updated(function ($user) {
            //update search for chat
            if ($user->name != $user->getOriginal('name')) {
                ChatRoomMember::updateUserName($user->id, $user->getName());
            }

            //clear cache phone number
            if ($user->phone_number != $user->getOriginal('phone_number')) {
                Cache::forget(self::getCacheKeyExistPhoneNumber($user->phone_number));
                Cache::forget(self::getCacheKeyExistPhoneNumber($user->getOriginal('phone_number')));
            }

            //update user page report
            if ($user->gender_id != $user->getOriginal('gender_id') || 
                $user->birthday != $user->getOriginal('birthday')) {
                UserPageFollowReportUpdate::add($user->id);
            }
            
            //update privacy
            $privacy = null;
            if ($user->privacy != $user->getOriginal('privacy')) {
                $privacy = $user->privacy;
            }

            if ($user->is_active != $user->getOriginal('is_active')) {                
                if ($user->is_active ) {
                    $privacy = $privacy = $user->privacy;
                } else {
                    $privacy = config('shaun_core.privacy.user.only_me');
                }
            }

            if ($privacy) {
                PostHome::updatePrivacy($user->id, $privacy);
                Post::updatePrivacy($user->id, $privacy);

                //story
                Story::updatePrivacy($user->id, $privacy);
            }
        });

        static::deleted(function ($user) {        
            //delete provider
            OpenidProviderUser::where('user_id', $user->id)->delete();

            //update ads
            Advertising::where('user_id', $user->id)->where('status', AdvertisingStatus::ACTIVE)->update(
                ['status' => AdvertisingStatus::STOP]
            );

            UserDelete::create([
                'user_id' => $user->id,
                'is_page' => $user->isPage()
            ]);

            Cache::forget(self::getCacheKeyExistPhoneNumber($user->phone_number));
        });
    }
}
