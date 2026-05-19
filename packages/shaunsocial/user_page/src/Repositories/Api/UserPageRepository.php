<?php

namespace Packages\ShaunSocial\UserPage\Repositories\Api;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Packages\ShaunSocial\Core\Models\User;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\UserFcmToken;
use Packages\ShaunSocial\Core\Repositories\Api\PostRepository;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Support\Facades\Subscription;
use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\UserPage\Enum\UserPageAdminRole;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageCategoryResource;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageFeaturePackageResource;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageResource;
use Packages\ShaunSocial\UserPage\Models\UserPageAdmin;
use Packages\ShaunSocial\UserPage\Models\UserPageCategory;
use Packages\ShaunSocial\UserPage\Models\UserPageFeaturePackage;
use Packages\ShaunSocial\UserPage\Models\UserPageFollowReport;
use Packages\ShaunSocial\UserPage\Models\UserPageInfo;
use Packages\ShaunSocial\UserPage\Models\UserPageReview;
use Packages\ShaunSocial\UserPage\Models\UserPageStatistic;
use Packages\ShaunSocial\UserPage\Models\UserPageToken;
use Packages\ShaunSocial\UserPage\Notification\UserPageAddAdminNotification;
use Packages\ShaunSocial\UserPage\Notification\UserPageReviewNotification;
use Packages\ShaunSocial\UserPage\Notification\UserPageTransferOwnerNotification;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Core\Http\Resources\User\UserProfileResource;
use Packages\ShaunSocial\Dating\Models\DatingAddress;
use Carbon\Carbon;
use Packages\ShaunSocial\UserPage\Models\UserPageCreateSubProfileFakePhoto as FakePhoto;

class UserPageRepository
{   
    use Utility, HasUserList, CacheSearchPagination;

    protected $postRepository;
    protected $userRepository;

    public function __construct(PostRepository $postRepository, UserRepository $userRepository)
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
    }

    public function store_profile($data, $viewer)
    {
        if (isset($data['name'])) {
            if ($viewer->name != $data['name'] && $viewer->verify_status == UserVerifyStatus::OK) {
                $viewer->doUnVerify(false);
            }
        }
        $viewer->update($data);
    }

    public function get_switch($page, $viewer)
    {
        if (! $viewer->isPage()) {
            return $this->get_my($page, $viewer);
        } else {
            $parent = $viewer->getParent();

            return $this->get_my($page, $parent, true);
        }
    }

    public function get_my($page, $viewer, $addViewer = false)
    {
        $users = UserPageAdmin::getCachePagination('user_page_my_'.$viewer->id, UserPageAdmin::where('user_id', $viewer->id)->orderBy('id', 'ASC'), $page);
        $pagesNextPage = UserPageAdmin::getCachePagination('user_page_my_'.$viewer->id, UserPageAdmin::where('user_id', $viewer->id)->orderBy('id', 'ASC'), $page + 1);
        $pages = $users->map(function ($item, $key) {
            return $item->getPage();
        })->filter(function ($item, $key){
            return $item;
        });

        $pages = $pages->filter(function ($value, $key) use ($viewer) {
            return $value->is_active && $viewer->id != $value->id;     
        });
        
        if ($addViewer && $page == 1) {
            $pages->prepend($viewer);
        }

        return [
            'items' => UserResource::collection($pages),
            'has_next_page' => count($pagesNextPage) ? true : false
        ];
    }

    public function get_admin($page, $viewer)
    {
        $admins = UserPageAdmin::getCachePagination('user_page_admin_'.$viewer->id, UserPageAdmin::where('user_page_id', $viewer->id)->orderBy('id', 'ASC'), $page);
        $adminsNextPage = UserPageAdmin::getCachePagination('user_page_admin_'.$viewer->id, UserPageAdmin::where('user_page_id', $viewer->id)->orderBy('id', 'ASC'), $page + 1);

        $users = $admins->filter(function ($admin, $key) {
            return ! $admin->isPageOwner();
        })->map(function ($item, $key) {
            return $item->getUser();
        });

        return [
            'items' => UserResource::collection($users),
            'has_next_page' => count($adminsNextPage) ? true : false
        ];
    }

    public function store_privacy($data, $viewer)
    {
        $settings = $viewer->getPrivacyPageInfoSetting();
        $settings[$data['type']] = $data['value'];
        $viewer->update([
            'page_info_privacy' => json_encode($settings)
        ]);
    }

    public function get_categories()
    {
        $categoryAll = UserPageCategory::getAll();
        $categories = $categoryAll->map(function ($category, $key) {
            if (! $category->canUse()) {
                return null;
            } else {
                if ($category->childs) {
                    $category->childs = $category->childs->map(function($category, $key) {
                        if (! $category->canUse()) {
                            return null;
                        } else {
                            return $category;
                        }
                    })->filter(function ($value, $key) {
                        return $value;
                    });
                }
                return $category;
            }
        })->filter(function ($value, $key) {
            return $value;
        });
        
        return UserPageCategoryResource::collection($categories);
    }

    public function store($data, $viewer)
    {
        $number = 0;
        do {                
            $email = $data['user_name'].$number.'@'.config('shaun_core.core.email_domain_default');

            $user = User::where('email', $email)->first();
            if (! $user) {
                break;
            }
            $number++;
        }
        while (true);

        $password = Str::random(10);

        $hashtagArray = [];
        if ($data['hashtags']) {
            foreach ($data['hashtags'] as $hashtag) {
                $item = Hashtag::firstOrCreate([
                    'name' => $hashtag,
                ]);
                $hashtagArray[] = $item->id;
            }
        }
        $roleId = config('shaun_core.role.id.member_default');
        $roleDefault = Role::getDefault();
        if ($roleDefault->isMember()) {
            $roleId = $roleDefault->id;
        }        
        $page = User::create([
            'name' => $data['name'],
            'email' => $email,
            'user_name' => $data['user_name'],
            'email_verified' => true,
            'password' => Hash::make($password),
            'has_email' => false,
            'is_page' => true,
            'page_hashtags' =>  Arr::join(array_unique($hashtagArray), ' '),
            'categories' => Arr::join(array_unique($data['categories']), ' '),
            'already_setup_login' => true,
            'is_active' => true, 
            'role_id' => $roleId,
            'identity_verified' => true,
            'about' => $data['description'],
        ]);

        $page->doVerify();

        UserPageAdmin::create([
            'user_id' => $viewer->id,
            'user_page_id' => $page->id,
            'role' => UserPageAdminRole::OWNER
        ]);

        UserPageInfo::create([
            'user_page_id' => $page->id,
            'description' => $data['description'],
        ]);

        return new UserProfileResource($page);
    }

    public function switch_page($userPageId, $viewer, $request)
    {
        $page = User::findByField('id', $userPageId);
        if (! $page->isPage()) {
            return $this->login_back($viewer, $request);
        } else {
            App::setLocale($page->language);
            $result = $this->loginUserBase($page, $request, $viewer->isPage() ? $viewer->getParent() : $viewer);
            [$id, $token] = explode('|', $result['token'], 2);
            
            UserPageToken::create([
                'user_id' => $viewer->isPage() ? $viewer->getParent()->id : $viewer->id,
                'token' => hash('sha256', $token),
                'user_page_id' => $userPageId
            ]);

            if ($request->has('fcm_token') && $request->fcm_token){
                $viewer->deleteFcmToken($request->fcm_token);
                
                UserFcmToken::create([
                    'user_id' => $userPageId,
                    'type' => $request->type,
                    'token' => $request->fcm_token
                ]);
            }

            return $result;
        }
    }

    public function store_description($description, $viewer)
    {
        $pageInfo = $viewer->getPageInfo();
        $pageInfo->update([
            'description' => $description
        ]);
    }

    public function store_address($data, $viewer)
    {
        $viewer->update($data);
    }

    public function store_phone_number($phone_number, $viewer)
    {
        $pageInfo = $viewer->getPageInfo();
        $pageInfo->update([
            'phone_number' => $phone_number
        ]);
    }

    public function store_email($email, $viewer)
    {
        $pageInfo = $viewer->getPageInfo();
        $pageInfo->update([
            'email' => $email
        ]);
    }

    public function login_back($viewer, $request)
    {        
        App::setLocale($viewer->getParent()->language);
        $result = $this->loginUserBase($viewer->getParent(), $request);

        if ($request->has('fcm_token')){
            $viewer->deleteFcmToken($request->fcm_token);
            
            UserFcmToken::create([
                'user_id' => $viewer->getParent()->id,
                'type' => $request->type,
                'token' => $request->fcm_token
            ]);
        }
        return $result;
    }

    public function store_category($categories, $viewer)
    {
        $viewer->update([
            'categories' => Arr::join(array_unique($categories), ' '),
        ]);
    }

    public function add_admin($userId, $viewer)
    {
        $user = User::findByField('id', $userId);
        UserPageAdmin::create([
            'user_id' => $userId,
            'user_page_id' => $viewer->id,
            'role' => UserPageAdminRole::ADMIN
        ]);

        Notification::send($user, $viewer->getParent(), UserPageAddAdminNotification::class, $viewer, [], 'shaun_user_page');

        return new UserResource($user);
    }

    public function transfer_owner($userId, $viewer, $request)
    {
        $user = User::findByField('id', $userId);

        $adminPage = $viewer->getAdminPage($user->id);
        if ($adminPage) {
            $adminPage->delete();
        }

        UserPageAdmin::create([
            'user_id' => $userId,
            'user_page_id' => $viewer->id,
            'role' => UserPageAdminRole::OWNER
        ]);

        Notification::send($user, $viewer->getParent(), UserPageTransferOwnerNotification::class, $viewer, [], 'shaun_user_page');

        $result = $this->login_back($viewer, $request);

        $currentAdminPage = $viewer->getPageAdminCurrentlyLogin();
        $currentAdminPage->delete();
        
        return $result;
    }

    public function remove_admin($userId, $viewer, $request)
    {
        $result = 'Ok';
        $parent = $viewer->getParent();
        if ($parent->id == $userId) {
            $result = $this->login_back($viewer, $request);
        }
        $admin = $viewer->getAdminPage($userId);
        $admin->delete();

        return $result;
    }

    public function store_hashtag($hashtags, $viewer)
    {
        $hashtagArray = [];
        if ($hashtags) {
            foreach ($hashtags as $hashtag) {
                $item = Hashtag::firstOrCreate([
                    'name' => $hashtag,
                ]);
                $hashtagArray[] = $item->id;
            }
        }

        $viewer->update([
            'page_hashtags' =>  Arr::join(array_unique($hashtagArray), ' '),
        ]);
    }

    public function store_price($price, $viewer)
    {
        $pageInfo = $viewer->getPageInfo();
        $pageInfo->update([
            'price' => $price
        ]);
    }

    public function store_websites($websites, $viewer)
    {
        $pageInfo = $viewer->getPageInfo();
       
        if ($websites) {
            $links = [];
            if (is_array($websites)) {
                foreach ($websites as $link) {
                    $links[] = [
                        'title' => ! empty($link['title']) ? $link['title'] : '',
                        'link' => $link['link']
                    ];
                }
            }
            $websites = json_encode($links);
        }

        $pageInfo->update([
            'websites' => $websites
        ]);

        return $pageInfo->getWebsites();
    }

    public function store_hour($data, $viewer) {
        $pageInfo = $viewer->getPageInfo();
        $hours = [
            'type' => $data['type']
        ];
        if ($hours['type'] == 'hours') {
            $hours['values'] = json_decode($data['values'], true);
        }
        $pageInfo->update([
            'open_hours' => json_encode($hours)
        ]);
    }

    public function store_enable_review($enable, $viewer)
    {
        $pageInfo = $viewer->getPageInfo();
        $pageInfo->update([
            'review_enable' => $enable
        ]);
    }

    public function get_all($data, $viewer)
    {
        $page = $data['page'];
        $builder = User::where('is_active', true)->where('is_page', true)->orderBy('is_page_feature', 'DESC')->orderBy('page_feature_view', 'ASC')->orderBy('id', 'DESC');
        if ($data['keyword']) {
            $builder->where('name', 'LIKE', '%'.$data['keyword'].'%');
        }
        if ($data['category']) {
            $builder->whereFullText('categories', $data['category']);
        }
        $pages = $this->getCacheSearchPagination('user_page_all_'.$data['keyword'].'_'.$data['category'], $builder, $page);
        $pagesNextPage = $this->getCacheSearchPagination('user_page_all_'.$data['keyword'].'_'.$data['category'], $builder, $page + 1);

        return [
            'items' => UserResource::collection($this->filterUserList($pages, $viewer, 'id')),
            'has_next_page' => count($pagesNextPage) ? true : false
        ];
    }

    public function get_for_you($page, $viewer)
    {
        $pages = collect();
        $pagesNextPage = [];

        $builder = User::where('is_active', true)->where('is_page', true)->orderBy('id', 'DESC');
        $hashtagRelative = $viewer->getHastagRelative();
        if ($hashtagRelative) {
            $builder->whereFullText('page_hashtags',implode(' ',$hashtagRelative));

            $pages = $this->getCacheSearchPagination('user_page_for_you_'.$viewer->id, $builder, $page);
            $pagesNextPage = $this->getCacheSearchPagination('user_page_for_you_'.$viewer->id, $builder, $page + 1);
        }

        return [
            'items' => UserResource::collection($this->filterUserList($pages, $viewer, 'id')),
            'has_next_page' => count($pagesNextPage) ? true : false
        ];
    }

    public function get_trending($page, $viewer)
    {
        $builder = User::where('is_active', true)->where('is_page', true);
        $builder->addSelect(DB::raw('(follower_count*'.config('shaun_core.trending_point.follower').' - DATEDIFF(CURRENT_DATE,created_at)*'.config('shaun_core.trending_point.diff_day').') as trending_order, id'));
        $builder->orderBy('trending_order', 'DESC')->orderBy('id', 'DESC');

        $results = $this->getCacheSearchPagination('user_page_trending', $builder, $page, setting('feature.item_per_page'), config('shaun_core.cache.time.trending'));
        $pagesNextPage = $this->getCacheSearchPagination('user_page_trending', $builder, $page + 1, setting('feature.item_per_page'), config('shaun_core.cache.time.trending'));

        $pages = collect();
        foreach ($results as $result) {
            $page = User::findByField('id', $result->id);
            if ($page) {
                $pages->push($page);
            }            
        }
        return [
            'items' => UserResource::collection($this->filterUserList($pages, $viewer, 'id')),
            'has_next_page' => count($pagesNextPage) ? true : false
        ];
    }

    public function store_review($data, $viewer)
    {
        $review = UserPageReview::create([
            'user_id' => $viewer->id,
            'user_page_id' => $data['page_id'],
            'is_recommend' => $data['is_recommend']
        ]);

        $postItem = PostItem::create([
            'user_id' => $viewer->id,
            'subject_type' => $review->getSubjectType(),
            'subject_id' => $review->id,
        ]);

        $post = $this->postRepository->store([
            'type' => 'user_page_review',
            'content' => $data['content'],
            'items' => [$postItem->id]
        ], $viewer);

        $review->update([
            'post_id' => $post->resource->id
        ]);
        
        $page = User::findByField('id', $data['page_id']);
        Notification::send($page, $viewer, UserPageReviewNotification::class, $post, [], 'shaun_user_page');

        return new PostResource($post);
    }

    public function get_reviews($pageId, $pageNumber, $viewer)
    {
        $page = User::findByField('id', $pageId);
        $pageInfo = $page->getPageInfo();

        $reviews = UserPageReview::getCachePagination('user_page_review_'.$pageId, UserPageReview::where('user_page_id', $pageId)->orderBy('id', 'DESC'), $pageNumber);
        $posts = collect();
        foreach ($reviews as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }            
        }

        return [
            'items' => PostResource::collection($this->filterUserList($posts, $viewer, 'user_id', ['privacy', 'active'])),
            'has_next_page' => checkNextPage($pageInfo->review_count, count($reviews), $pageNumber)
        ];
    }

    public function get_report_overview($page)
    {
        $data = Cache::remember('report_overview_'.$page->id, config('shaun_user_page.report_cache_time'), function () use ($page){
            $date = now()->subDays(config('shaun_user_page.report_sub_day'));
            $postLikeCount = UserPageStatistic::where('type', 'post_like')->where('user_page_id', $page->id)->where('created_at', '>=', $date)->count();
            $postCommentCount = UserPageStatistic::where('type', 'post_comment')->where('user_page_id', $page->id)->where('created_at', '>=', $date)->count();
            $postShareCount = UserPageStatistic::where('type', 'post_share')->where('user_page_id', $page->id)->where('created_at', '>=', $date)->count();
            return [
               'post_reach' => UserPageStatistic::where('type', 'post_reach')->where('user_page_id', $page->id)->where('created_at', '>=', $date)->count(),
               'post_comment' => $postCommentCount,
               'post_share' => $postShareCount,
               'post_engagement' => $postCommentCount + $postShareCount + $postLikeCount,
               'follow' => UserPageStatistic::where('type', 'follow')->where('user_page_id', $page->id)->where('created_at', '>=', $date)->count()
            ];
        });

        return $data;
    }

    public function get_report_audience($page)
    {
        $data = Cache::remember('report_audience_'.$page->id, config('shaun_user_page.report_cache_time'), function () use ($page) {
            $genderReports = UserPageFollowReport::groupBy('gender_id')->where('user_page_id', $page->id)->select('gender_id', DB::raw('count(*) as total'))->get();
            $results = [];
            $genders = Gender::getAll();
            $total = 0;
            $reports = [];
            foreach ($genders as $gender) {
                $results['genders'][] = [
                    'name' => $gender->getTranslatedAttributeValue('name'),
                    'color' => $gender->getColorReport()
                ];
            }

            $results['genders'][] = [
                'name' => __('Unknown'),
                'color' => config('shaun_core.gender.report_unknown_color')
            ];

            foreach ($genderReports as $genderReport) {
                $total += $genderReport->total;
                $reports[$genderReport->gender_id] = $genderReport->total;
            }

            $countExist = 0;
            $percentExist = 0;
            foreach ($genders as $gender) {
                $count = isset($reports[$gender->id]) ? $reports[$gender->id] : 0;
                $countExist += $count;
                $percent = $total ? round(($count / $total) * 100) : 0;
                $percentExist += $percent;
                $results['follows'][] = [
                    'name' => $gender->getTranslatedAttributeValue('name'),
                    'total' => $count,
                    'percent' => $percent,
                    'color' => $gender->getColorReport()
                ];
            }

            $results['follows'][] = [
                'name' => __('Unknown'),
                'total' => $total - $countExist,
                'percent' => $total ? (100 - $percentExist) : 0,
                'color' => config('shaun_core.gender.report_unknown_color')
            ];

            $ages = [
                [
                    'start' => 0,
                    'end' => 17,
                ],
                [
                    'start' => 18,
                    'end' => 24,
                ],
                [
                    'start' => 25,
                    'end' => 34,
                ],
                [
                    'start' => 35,
                    'end' => 44,
                ],
                [
                    'start' => 45,
                    'end' => 54,
                ],
                [
                    'start' => 55,
                    'end' => 64,
                ],
                [
                    'start' => 65,
                    'end' => 0,
                ]
            ];

            $total = 0;
            $totalAges = [];
            $agesGenders = [];
            foreach ($ages as $age) {
                $startDate = now()->subYears($age['start'])->format('Y');
                $endDate = $age['end'] ? now()->subYears($age['end'])->format('Y') : 0;
                
                $builder = UserPageFollowReport::groupBy('gender_id')->where('user_page_id', $page->id)->select('gender_id', DB::raw('count(*) as total'))->where('birthday','<=', $startDate);
                if ($endDate) {
                    $builder->where('birthday', '>=', $endDate);
                }
                $key = $age['start'].'_'.$age['end'];
                $genderReports = $builder->get();
                $totalAges[$key] = 0;
                foreach ($genderReports as $genderReport) {
                    $total += $genderReport->total;
                    $totalAges[$key] += $genderReport->total;
                    $agesGenders[$key][$genderReport->gender_id] = $genderReport->total;
                }
            }

            $percentExist = 0;
            foreach ($ages as $start => $age) {
                $key = $age['start'].'_'.$age['end'];
                $keyName = $age['start'].'-'.$age['end'];
                if (! $age['end']) {
                    $keyName = $age['start'].'+';
                }
                $countExist = 0;
                foreach ($genders as $gender) {
                    $count = isset($agesGenders[$key][$gender->id]) ? $agesGenders[$key][$gender->id] : 0;
                    $countExist += $count;
                    $percent = $total ? round(($count / $total) * 100) : 0;
                    $percentExist += $percent;
                    $results['ages'][$keyName][] = [
                        'name' => $gender->getTranslatedAttributeValue('name'),
                        'total' => $count,
                        'percent' => $percent,
                        'color' => $gender->getColorReport()
                    ];
                }

                $results['ages'][$keyName][] = [
                    'name' => __('Unknown'),
                    'total' => $totalAges[$key] - $countExist,
                    'percent' => $total ? (($start == count($ages) - 1) ? (100 - $percentExist) : (round((($totalAges[$key] - $countExist) / $total) * 100))) : 0,
                    'color' => config('shaun_core.gender.report_unknown_color')
                ];
            }

            return $results;
        });

        return $data;
    }

    public function get_notify_setting($viewer)
    {
        $adminPage = $viewer->getPageAdminCurrentlyLogin();
        return $adminPage->getNotifySetting();
    }

    public function store_notify_setting($data, $viewer)
    {
        $adminPage = $viewer->getPageAdminCurrentlyLogin();
        $notifySettingKeys = array_keys($adminPage->getNotifySetting());
        $notifySettings = [];
        foreach ($notifySettingKeys as $key) {
            $notifySettings[$key] = $data[$key];
        }
        $adminPage->update(['notify_setting' => json_encode($notifySettings)]);
    }

    public function store_general_setting($data, $viewer)
    {
        $viewer->update($data);
    }

    public function delete($viewer, $request)
    {
        $result = $this->login_back($viewer, $request);
        $this->userRepository->delete($viewer);

        return $result;
    }

    public function get_feature_packages()
    {
        return UserPageFeaturePackageResource::collection(UserPageFeaturePackage::getAll());
    }

    public function store_feature($packageId, $viewer)
    {
        $package = UserPageFeaturePackage::findByField('id', $packageId);

        DB::beginTransaction();
        $result = ['status' => false];
        try {
            $subscription = Subscription::create($viewer, 'page_feature', getWalletTokenName(), config('shaun_gateway.wallet_id'), $viewer, $package);
            $userResult = Wallet::transferSubscription($subscription);
            if ($userResult['status']) {
                $transaction = $userResult['from_transaction'];
                $subscription->doActive([], true, $package->amount, $transaction->getId());
                $result['status'] = true;
                DB::commit();
            } else {
                $result['message'] = __("You don't have enough balance");
                DB::rollBack();
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }
        
        return $result;
    }

    public function get_feature($viewer, $page, $limit = null)
    {
        $builder = User::where('is_active', true)->where('is_page_feature', true)->orderBy('page_feature_view', 'ASC')->orderBy('id', 'DESC');
        $pages = $this->getCacheSearchPagination('user_page_feature', $builder, $page, $limit);
        $pages = $this->filterUserList($pages, $viewer, 'id');
        $pages->each(function($page){
            $lock = Cache::lock('user_page_feature_count_'.$page->id, config('shaun_user_page.feature_count_lock_time'));
            if ($lock->get()) {
                $page->update([
                    'page_feature_view' => $page->page_feature_view + 1
                ]);
            }
        });
        return UserResource::collection($pages);
    }

    public function search_post($id, $query, $page ,$viewer)
    {
        $builder = Post::where('user_id', $id)->where('show', true)->where('has_source', false)->whereFullText('content_search', $query)->orderBy('created_at', 'DESC');
        $posts = $this->getCacheSearchShortPagination('user_page_get_post_search_'.$id.'_'.$query, $builder, $page);
        
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        
        return PostResource::collection($posts);
    }

    public function get_post_with_hashtag($id, $name, $page, $viewer)
    {
        $hashtag = Hashtag::findByField('name', $name);
        $builder = Post::where('user_id', $id)->where('show', true)->where('has_source', false)->whereFullText('hashtags', $hashtag->id)->orderBy('created_at', 'DESC');
        $posts = $this->getCacheSearchShortPagination('user_page_get_post_hashtag_'.$id.'_'.$name, $builder, $page);
        
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        
        return PostResource::collection($posts);
    }

    public function store_fake_profile($data, $userId)
    {
        $age = $data['age'];
        $today = Carbon::now(); 
        $maxBirth = $today->copy()->subYears($age); 
        $minBirth = $today->copy()->subYears($age + 1)->addDay(); 
        $birthday = Carbon::createFromTimestamp(rand($minBirth->timestamp, $maxBirth->timestamp));

        $number = 0;
        do {                
            $email = $data['user_name'].$number.'@'.config('shaun_core.core.email_domain_default');

            $user = User::where('email', $email)->first();
            if (! $user) {
                break;
            }
            $number++;
        }
        while (true);

        $password = Str::random(10);

        if(!$data['state_id']){
            $data['state_id'] = 0;
        }

        if(!$data['city_id']){
            $data['city_id'] = 0;
        }

        $page = User::create([
            'name' => $data['name'],
            'user_name' => $data['user_name'],
            'about' => $data['about'],
            'role_id' => $data['role_id'],
            'gender_id' => $data['gender_id'],
            'country_id' => $data['country_id'],
            'state_id' => $data['state_id'],
            'city_id' => $data['city_id'],
            'attributes' => $data['attributes'],
            'interest_attributes' => $data['interest_attributes'],
            'birthday' => $birthday->toDateString(),
            'fake_user' => true,
            'password' => Hash::make($password),
            'email' => $email,
            'email_verified' => true,
            'has_email' => false,
            'is_page' => true,
            'already_setup_login' => true,
            'is_active' => true, 
            'identity_verified' => true,
        ]);

        if ($data['country_id']) {
            $address = DatingAddress::findAddress($data['country_id'], $data['state_id'], $data['city_id']);
            $data['dating_addresses_id'] = $address?->id ?? 0;
            $data['dating_addresses_fulltext'] = $page->getAddessFull() ?? null;
            $page->update($data);
        }

        $page->doVerify();

        UserPageAdmin::create([
            'user_id' => $userId,
            'user_page_id' => $page->id,
            'role' => UserPageAdminRole::OWNER
        ]);

        UserPageInfo::create([
            'user_page_id' => $page->id,
            'description' => $data['about'],
        ]);

        $photo = FakePhoto::where('gender', $data['gender_id'] == 1 ? 'male' : 'female')->where('user_id', 0)->orderBy('id', 'desc')->first();
        if ($photo) {
            $photo->update(['user_id' => $page->id]);
        }
    }
}
