<?php


namespace Packages\ShaunSocial\UserPage\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\PageValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\AddAdminValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\RemoveAdminValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreCategoryValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreDescriptionValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreEmailValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreHashtagValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StorePageEditProfileValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StorePagePrivacyValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StorePageValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StorePhoneNumberValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\SwitchPageValidate;
use Packages\ShaunSocial\UserPage\Repositories\Api\UserPageRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Core\Repositories\Api\FollowRepository;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\UserPage\Http\Requests\DeletePageValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\GetPageAllValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\GetPostWithHashtagValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\GetReviewValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\SearchPostValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreAddressValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreEnableReviewValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreFeatureValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreGeneralSettingValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreHourValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreNotifySettingValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StorePriceValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreReviewValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\StoreWebsitesValidate;
use Packages\ShaunSocial\UserPage\Http\Requests\TransferOwnerValidate;

class UserPageController extends ApiController
{
    protected $userPageRepository;
    protected $followRepository;
    protected $userRepository;

    public function __construct(UserPageRepository $userPageRepository, FollowRepository $followRepository, UserRepository $userRepository)
    {
        if (! setting('shaun_user_page.enable')) {
            $current = Route::getCurrentRoute();
            $blackList = [
                'user_page_store',
            ];
            
            if (in_array($current->getActionMethod(), $blackList)) {
               throw new MessageHttpException(__('Do not support this method.'));
            }
            
        }
        $this->userPageRepository = $userPageRepository;
        $this->followRepository = $followRepository;
        $this->userRepository = $userRepository;
    }

    public function get_categories()
    {
        $result = $this->userPageRepository->get_categories();

        return $this->successResponse($result);
    }

    public function store_privacy(StorePagePrivacyValidate $request)
    {
        $this->userPageRepository->store_privacy($request->safe()->all(), $request->user());

        return $this->successResponse();
    }

    public function store_profile(StorePageEditProfileValidate $request)
    {
        $this->userPageRepository->store_profile($request->safe()->all(), $request->user());

        return $this->successResponse();
    }

    public function get_my(UserValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->userPageRepository->get_my($page, $request->user());

        return $this->successResponse($result);
    }

    public function get_switch(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->userPageRepository->get_switch($page, $request->user());

        return $this->successResponse($result);
    }
    
    public function get_admin(PageValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->userPageRepository->get_admin($page, $request->user());

        return $this->successResponse($result);
    }

    public function store(StorePageValidate $request)
    {
        $request->mergeIfMissing([
            'hashtags' => [],
            'description' => '',
        ]);

        $result = $this->userPageRepository->store($request->only([
            'name', 'user_name', 'categories', 'hashtags', 'description'
        ]), $request->user());

        UserActionLog::create([
            'user_id' => $request->user()->id,
            'type' => 'create_page'
        ]);

        return $this->successResponse($result);
    }

    public function switch_page(SwitchPageValidate $request)
    {
        $result = $this->userPageRepository->switch_page($request->id, $request->user(), $request);

        return $this->accessTokenMessageRespone($result, $result['token']);
    }

    public function store_description(StoreDescriptionValidate $request)
    {
        $request->mergeIfMissing([
            'description' => '',
        ]);

        $this->userPageRepository->store_description($request->description, $request->user());

        return $this->successResponse();
    }

    public function store_address(StoreAddressValidate $request)
    {
        $request->mergeIfMissing([
            'address' => '',
        ]);
        $data = $request->safe()->all();
        $countryData = getCountryData();
        $request->mergeIfMissing($countryData);

        $data = array_merge($data, $request->all(array_keys($countryData)));
        $data = cleanCountryData($data);
        $data['location'] = $data['address'];

        $this->userPageRepository->store_address($data, $request->user());

        return $this->successResponse();
    }
    
    public function store_phone_number(StorePhoneNumberValidate $request)
    {
        $request->mergeIfMissing([
            'phone_number' => '',
        ]);

        $this->userPageRepository->store_phone_number($request->phone_number, $request->user());

        return $this->successResponse();
    }

    public function store_email(StoreEmailValidate $request)
    {
        $request->mergeIfMissing([
            'email' => '',
        ]);

        $this->userPageRepository->store_email($request->email, $request->user());

        return $this->successResponse();
    }

    public function login_back(PageValidate $request)
    {
        $result = $this->userPageRepository->login_back($request->user(), $request);

        return $this->accessTokenMessageRespone($result, $result['token']);
    }

    public function store_category(StoreCategoryValidate $request)
    {
        $this->userPageRepository->store_category($request->categories, $request->user());

        return $this->successResponse();
    }

    public function add_admin(AddAdminValidate $request)
    {
        $result = $this->userPageRepository->add_admin($request->id, $request->user());

        return $this->successResponse($result);
    }

    public function transfer_owner(TransferOwnerValidate $request)
    {
        $result = $this->userPageRepository->transfer_owner($request->id, $request->user(), $request);

        return $this->accessTokenMessageRespone($result, $result['token']);
    }

    public function remove_admin(RemoveAdminValidate $request)
    {
        $result = $this->userPageRepository->remove_admin($request->id, $request->user(), $request);

        if (is_array($result)) {
            return $this->accessTokenMessageRespone($result, $result['token']);
        } else {
            return $this->successResponse($result);
        }
    }

    public function store_hashtag(StoreHashtagValidate $request)
    {
        $this->userPageRepository->store_hashtag($request->hashtags, $request->user());

        return $this->successResponse();
    }

    public function get_prices(PageValidate $request)
    {
        return $this->successResponse(getPageInfoPriceList());
    }

    public function store_price(StorePriceValidate $request)
    {
        $this->userPageRepository->store_price($request->price, $request->user());

        return $this->successResponse();
    }

    public function store_websites(StoreWebsitesValidate $request)
    {
        $result = $this->userPageRepository->store_websites($request->websites, $request->user());

        return $this->successResponse($result);
    }

    public function get_hours(PageValidate $request)
    {
        return $this->successResponse(getPageInfoHourList());
    }

    public function store_hour(StoreHourValidate $request)
    {
        $this->userPageRepository->store_hour($request->all(), $request->user());

        return $this->successResponse();
    }

    public function store_enable_review(StoreEnableReviewValidate $request)
    {
        $this->userPageRepository->store_enable_review($request->enable, $request->user());

        return $this->successResponse();
    }

    public function get_all(GetPageAllValidate $request)
    {
        $request->mergeIfMissing([
            'page' => 1,
            'keyword' => '',
            'category' => ''
        ]);
        $result = $this->userPageRepository->get_all($request->only(['page', 'keyword', 'category']), $request->user());

        return $this->successResponse($result);
    }

    public function get_for_you(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->userPageRepository->get_for_you($page, $request->user());

        return $this->successResponse($result);
    }

    public function get_following(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->followRepository->user_get_following($request->user()->id, $page, 'page', $request->user());

        return $this->successResponse($result);
    }

    public function get_trending(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->userPageRepository->get_trending($page, $request->user());

        return $this->successResponse($result);
    }

    public function store_review(StoreReviewValidate $request)
    {
        $request->mergeIfMissing([
            'content' => '',
            'is_recommend' => false
        ]);

        $result = $this->userPageRepository->store_review($request->only([
            'content', 'is_recommend', 'page_id'
        ]), $request->user());

        return $this->successResponse($result);
    }

    public function get_reviews(GetReviewValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->userPageRepository->get_reviews($request->page_id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function get_report_overview(PageValidate $request)
    {
        $result = $this->userPageRepository->get_report_overview($request->user());

        return $this->successResponse($result);
    }

    public function get_report_audience(PageValidate $request)
    {
        $result = $this->userPageRepository->get_report_audience($request->user());

        return $this->successResponse($result);
    }

    public function get_notify_setting(PageValidate $request)
    {
        $result = $this->userPageRepository->get_notify_setting($request->user());

        return $this->successResponse($result);
    }

    public function store_notify_setting(StoreNotifySettingValidate $request)
    {
        $this->userPageRepository->store_notify_setting($request->safe()->all(), $request->user());

        return $this->successResponse();
    }

    public function suggest_user_for_admin(PageValidate $request)
    {
        $result = $this->userRepository->search($request->text,[
            'only_user' => true
        ], $request->user());

        return $this->successResponse($result);
    }

    public function suggest_user_for_transfer(PageValidate $request)
    {
        $result = $this->userRepository->search($request->text,[
            'only_user' => true,
            'not_me' => true,
            'not_parent' => true
        ], $request->user());

        return $this->successResponse($result);
    }

    public function store_general_setting(StoreGeneralSettingValidate $request)
    {
        $this->userPageRepository->store_general_setting($request->only([
            'name', 'user_name'
        ]), $request->user());

        return $this->successResponse();
    }

    public function delete(DeletePageValidate $request)
    {
        $result = $this->userPageRepository->delete($request->user(), $request);

        return $this->accessTokenMessageRespone($result, $result['token']);
    }

    public function get_feature_packages(PageValidate $request)
    {
        $result = $this->userPageRepository->get_feature_packages();

        return $this->successResponse($result);
    }

    public function store_feature(StoreFeatureValidate $request)
    {
        $result = $this->userPageRepository->store_feature($request->package_id, $request->user());

        return $this->successResponse($result);
    }

    public function get_feature(Request $request)
    {
        $result = $this->userPageRepository->get_feature($request->user(), 1);

        return $this->successResponse($result);
    }

    public function search_post(SearchPostValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->userPageRepository->search_post($request->id, $request->query('query'), $page, $request->user());

        return $this->successResponse($result);
    }

    public function get_post_with_hashtag(GetPostWithHashtagValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->userPageRepository->get_post_with_hashtag($request->id, $request->name, $page, $request->user());

        return $this->successResponse($result);
    }
}