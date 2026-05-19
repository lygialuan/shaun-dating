<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Resources\TwoFactor\TwoFactorProviderResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\CurrencyResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\OpenidProviderResource;
use Packages\ShaunSocial\Core\Models\Currency;
use Packages\ShaunSocial\Core\Models\LayoutMap;
use Packages\ShaunSocial\Core\Models\OpenidProvider;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Traits\ApiResponser;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Gateway\Http\Resources\GatewayResource;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Dating\Models\DatingProfileCompletionSetting;
use Packages\ShaunSocial\Gift\Models\Gift;
use Packages\ShaunSocial\Gift\Http\Resources\GiftResource;

class AppController extends ApiController
{
    use ApiResponser, Utility;
    
    public function getWhitelistForceLogin()
    {
        return [
            'init',
            'layout'
        ];
    }

    public function csrf(Request $request)
    {
       return $this->successResponse([
            'csrf' => csrf_token()
        ]);
    }

    public function init(Request $request)
    {    
        $offline = setting('site.offline') ? true : false;
        if ($offline && $request->headers->get('Access-Code') == setting('site.offline_code')) {
            $offline = false;
        }

        $storyTimeout = setting('story.time_next_story');
        if (! $storyTimeout) {
            $storyTimeout = config('shaun_story.story_timeout_default');
        }

        $fullScreenAdsNumberClick = setting('mobile.fullscreen_ads_number_click');
        if (! $fullScreenAdsNumberClick) {
            $fullScreenAdsNumberClick = config('shaun_app.admod.full_ads_default_click_show');
        }

        $broadcast = [
            'broadcastKey' => setting('broadcast.key'),
            'broadcastCluster' => setting('broadcast.cluster'),
            'broadcastHost' => setting('broadcast.host'),
            'broadcastPort' => setting('broadcast.port'),
            'broadcastScheme' => setting('broadcast.scheme'),
            'broadcastForceTLS' => setting('broadcast.force_tls') ? true : false,
        ];

        if (env('CLOUD_ENABLE')) {
            $broadcast = [
                'broadcastKey' => env('PUSHER_APP_KEY'),
                'broadcastCluster' => env('PUSHER_APP_CLUSTER'),
                'broadcastHost' => env('PUSHER_HOST'),
                'broadcastPort' => env('PUSHER_PORT'),
                'broadcastScheme' => env('PUSHER_SCHEME'),
                'broadcastForceTLS' => env('PUSHER_SCHEME', 'https') === 'https'
            ];
        }

        $currencyDefault = Currency::getDefault();     
        
        $languages = Language::getAll(false);
        $keyLanguages = $languages->mapWithKeys(function ($lang) {
            return [$lang->key => $lang->getTranslatedAttributeValue('name')];
        })->all();

        $datingProfileCompletionSetting = DatingProfileCompletionSetting::findByField('id', 1);

        $result = [
            'config' => [
                'siteName' => setting('site.title'),
                'emailVerify' => setting('feature.email_verify') ? true : false,
                'phoneVerify' => setting('feature.phone_verify') ? true : false,
                'itemPerPage' => setting('feature.item_per_page'),
                'inviteMax' => getInviteMax(),
                'shareEmailMax' => getShareEmailMax(),
                'offline' => $offline,
                'offlineMessage' => setting('site.offline_message'),
                'broadcastEnable' => setting('broadcast.enable') ? true : false,
                'notificationInterval' => setting('site.notification_interval') * 1000,
                'signupEnableRecapcha' => setting('spam.signup_enable_recapcha') ? true : false,
                'loginEnableRecapcha' => setting('spam.login_enable_recapcha') ? true : false,
                'contactEnableRecapcha' => setting('spam.contact_enable_recapcha') ? true : false,
                'shareEmailEnableRecapcha' => setting('spam.share_email_enable_recapcha') ? true : false,
                'inviteEmailEnableRecapcha' => setting('spam.invite_email_enable_recapcha') ? true : false,
                'sendPhoneEnableRecapcha' => setting('spam.send_phone_enable_recapcha') ? true : false,
                'cookieEnable' => setting('feature.cookie_enable') ? true : false,
                'cookieLink' => setting('feature.cookie_link'),
                'signupEnable' => setting('feature.enable_signup') ? true : false,
                'ffmegEnable'=> setting('feature.ffmpeg_enable') ? true : false,
                'story' => [
                    'timeout' => $storyTimeout,
                    'shareUserMax' => getStoryShareUserMax()
                ],
                'limitVideoDuration' => setting('feature.video_max_duration'),
                'limitAudioDuration' => setting('feature.audio_max_duration'),
                'videoDurationConvertNow' => config('shaun_core.video.limit_duration_convert_now'),
                'photoExtensionSupport' => config('shaun_core.validation.photo'),
                'videoExtensionSupport' => getVideoExtensions(),
                'csvExtensionSupport' => config('shaun_core.validation.csv'),
                'openidProviders' => OpenidProviderResource::collection(OpenidProvider::getAll()),
                'maxUploadSize' => getMaxUploadFileSize(),
                'enableBottomBannerAds' => setting('mobile.enable_bottom_banner_ads') ? true : false,                
                'enableFullScreenAds' => setting('mobile.enable_fullscreen_ads') ? true : false,
                'fullScreenAdsNumberClick' => $fullScreenAdsNumberClick,                
                'enableAndroidLink' => setting('site.enable_android_link') ? true : false,
                'androidLink' => setting('site.android_link'),
                'enableIosLink' => setting('site.enable_ios_link') ? true : false,
                'iosLink' => setting('site.ios_link'),
                'appSuggestPhoto' => setting('site.app_suggest_photo'),
                'userVerifyEnable' => setting('user_verify.enable') ? true : false,
                'userVerifyExtensionSupport' => removeSpaceString(setting('user_verify.support_files')),
                'chatExtensionSupport' => removeSpaceString(setting('chat.support_files')),
                'userVerifyBadgeIcon' => setting('user_verify.badge'),
                'userPageVerifyBadgeIcon' => setting('user_verify.user_page_badge'),
                'userVerifyLostWhen' => setting('user_verify.unverify_when'),
                'wallet' => [
                    'enable' => setting('shaun_wallet.enable') ? true : false,
                    'exchangeRate' => getWalletExchangeRate(),
                    'tokenName' => getWalletTokenName(),
                    'fundTransferEnable' => checkEnableFundTransfer(),
                    'fundTransferVerifyEnable' => setting('shaun_wallet.fund_transfer_verify_enable') ? true : false,
                    'fundTransferPaypalEnable' => setting('shaun_wallet.fund_transfer_paypal_enable') ? true : false,
                    'fundTransferPaypalMinimum' => setting('shaun_wallet.fund_transfer_paypal_minimum'),
                    'fundTransferPaypalFee' => setting('shaun_wallet.fund_transfer_paypal_fee'),                    
                    'fundTransferBankEnable' => setting('shaun_wallet.fund_transfer_bank_enable') ? true : false,
                    'fundTransferBankMinimum' => setting('shaun_wallet.fund_transfer_bank_minimum'),
                    'fundTransferBankFee' => setting('shaun_wallet.fund_transfer_bank_fee'),
                ],
                'user_page' => [
                    'enable' => setting('shaun_user_page.enable') ? true : false,
                    'featureEnable' => setting('shaun_user_page.feature_enable') ? true : false,
                    'featureBadgeIcon' => setting('shaun_user_page.feature_badge'),
                    'featureImage' => setting('shaun_user_page.feature_image'),
                    'featureImageDark' => setting('shaun_user_page.feature_image_dark')
                ],
                'advertising' => [
                    'enable' => setting('shaun_advertising.enable') ? true : false,
                    'vat' => setting('shaun_advertising.vat')
                ],
                'vibb' => [
                    'enable' => setting('shaun_vibb.enable') ? true : false
                ],
                'group' => [
                    'enable' => setting('shaun_group.enable') ? true : false
                ],
                'currency' => new CurrencyResource($currencyDefault),
                'permissionMessages' => getPermissionMessagesForApi(),
                'membership'  => [
                    'enable' => setting('shaun_user_subscription.enable') ? true : false
                ],
                'chat' => [
                    'enable_bubble_chat' => setting('chat.enable_bubble_chat') ? true : false,
                    'enable_gifs' => setting('chat.enable_gifs') ? true : false
                ],
                'post' => [
                    'enable_gifs' => setting('post.enable_gifs') ? true : false,
                    'enable_file' => setting('post.upload_files_enable') ? true : false,
                    'file_extensions' => removeSpaceString(setting('post.support_files')),
                    'photo_max' => setting('post.post_photo_max'),
                    'file_max' => setting('post.post_file_max'),
                    'character_max' => getMaxTextSql(setting('feature.post_character_max')),
                ],
                'paid_content' => [
                    'enable' => setting('shaun_paid_content.enable') ? true : false,
                    'require_verify' => setting('shaun_paid_content.require_verify') ? true : false,
                    'commission_fee' => setting('shaun_paid_content.commission_fee'),
                    'commission_referral' => setting('shaun_paid_content.commission_referral')
                ],
                'two_factor' => [
                    'enable' => setting('feature.enable_two_factor') ? true : false,
                    'providers' => TwoFactorProviderResource::collection(TwoFactorProvider::getAll())
                ],
                'languages' => $keyLanguages,
                'chatbot' => [
                    'enable' => setting('ai_features.chatbot_enable')
                ],
                'pwa' => getConfgPWA(),
                'photosVerify' => setting('feature.photos_verify') ? true : false,
                'limitPhotosVerify' => setting('feature.limit_photos_verify'),
                'identityVerify' => setting('feature.identity_verify') ? true : false,
                'messPermissionSwipe'=> Permission::getMessageErrorByKey('dating.maximum_number_of_right_swipes'),
                'present_profile_basic_info'=> $datingProfileCompletionSetting?->basic_info ?? 0,
                'present_profile_about'=> $datingProfileCompletionSetting?->about ?? 0,
                'present_profile_work_education'=> $datingProfileCompletionSetting?->work_education ?? 0,
                'present_profile_more_about'=> $datingProfileCompletionSetting?->more_about ?? 0,
                'present_profile_interests'=> $datingProfileCompletionSetting?->interests ?? 0,
                'present_profile_social_profiles'=> $datingProfileCompletionSetting?->social_profiles ?? 0,
                'present_profile_is_active'=> $datingProfileCompletionSetting?->is_active,
                'avatar_default' => setting('feature.avatar_default'),
                'gifts'=> GiftResource::collection(Gift::getAll()),
                'ai_chat_profiles' => [
                    'enable' => setting('ai_chat_profiles.enable') ? true : false,
                ],
                'dating' => [
                    'sign_up_photo_one' => setting('shaun_dating.sign_up_photo_one'),
                    'sign_up_photo_two' => setting('shaun_dating.sign_up_photo_two'),
                    'sign_up_photo_three' => setting('shaun_dating.sign_up_photo_three'),
                ],
                'gift' => [
                    'enable' => setting('shaun_gift.enable') ? true : false
                ]
            ],
        ];

        $result['config'] += $broadcast;

        if ($request->headers->get('SupportCookie')) {
            $result += [
                'menus' => [
                    'main' => $this->getMenu($request, 'main'),
                    'footer' => $this->getMenu($request, 'footer'),
                    'profile' => $this->getMenu($request, 'profile'),
                ],
                'layouts' => [
                    'header' => $this->getLayoutContent($request, config('shaun_core.layout.header_footer_id'), 'header'),
                    'footer' => $this->getLayoutContent($request, config('shaun_core.layout.header_footer_id'), 'footer'),
                ],
                'gateways' => GatewayResource::collection($this->getGateways($currencyDefault->code))
            ];
        }

        return $this->successResponse($result);
    }

    public function layout(Request $request)
    {
        $router = $request->get('router', '');
        $layoutClass = LayoutMap::getLayout($router);
        if (! $layoutClass) {
            return $this->errorNotFound(__('Router not found.'));
        }

        return app($layoutClass)->render($request);
    }

    public function get_mobile_menu(Request $request)
    {
        return $this->successResponse($this->getMenu($request, 'mobile_menu'));
    }
}
