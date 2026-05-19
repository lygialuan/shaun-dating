<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Http\Requests\User\AdminChangeLanguageValidate;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\Report;
use Packages\ShaunSocial\Core\Models\Sanctum\PersonalAccessToken;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Group\Models\Group;

class DashboardController extends Controller
{
    protected $userRepository = null;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        $data = Cache::remember('dashboard_count', config('shaun_core.cache.time.dashboard_count'), function () {
            return [
                'userOnlineCount' => PersonalAccessToken::where('is_page', false)->where('last_used_at', '>', now()->subSeconds(config('shaun_core.cache.time.access_token_update')))->selectRaw('COUNT(DISTINCT tokenable_id) as online_count')->first()->online_count,
                'userCount' => User::where('is_page', false)->count(),
                'userActiveCount' => User::where('is_page', false)->where('has_active', true)->count(),
                'pageOnlineCount' => PersonalAccessToken::where('is_page', true)->where('last_used_at', '>', now()->subSeconds(config('shaun_core.cache.time.access_token_update')))->selectRaw('COUNT(DISTINCT tokenable_id) as online_count')->first()->online_count,
                'pageCount' => User::where('is_page', true)->count(),
                'pageActiveCount' => User::where('is_page', true)->where('has_active', true)->count(),
                'fileCount' => StorageFile::count(),
                'groupCount' => Group::count(),
                'postCount' => Post::count(),
                'hashtagCount' => Hashtag::count(),
                'reportCount' => Report::count()
            ];
        });
        extract($data);
        
        $userInActiveCount = $userCount - $userActiveCount;
        $pageInActiveCount = $pageCount - $pageActiveCount;

        $title = __('Dashboard');

        return view('shaun_core::admin.dashboard.index', compact('title', 'userCount', 'userActiveCount', 'userInActiveCount', 'fileCount', 'postCount', 'hashtagCount', 'reportCount', 'userOnlineCount', 'pageOnlineCount', 'pageCount', 'pageActiveCount', 'pageInActiveCount', 'groupCount'));
    }

    public function clear_cache()
    {
        clearAllCache();

        return redirect()->back()->with([
            'admin_message_success' => __('All caches have been cleared.'),
        ]);
    }

    public function chart()
    {
        $start = now()->subDays(config('shaun_core.core.chart_day') + 1);
        $users = [];
        $pages = [];
        $posts = [];
        $reports = [];
        $groups = [];
        $dayTime = 86400;

        for ($i = 0; $i <= config('shaun_core.core.chart_day'); $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toFormattedDateString();
            $data = Cache::remember('dashboard_chart_'.$key, $dayTime, function () use ($date) {
                return [
                    'user' => User::whereBetween('created_at', [$date->startOfDay(), $date->copy()->endOfDay()])->where('is_page', false)->count(),
                    'page' => User::whereBetween('created_at', [$date->startOfDay(), $date->copy()->endOfDay()])->where('is_page', true)->count(),
                    'post' => Post::whereBetween('created_at', [$date->startOfDay(), $date->copy()->endOfDay()])->count(),
                    'report' => Report::whereBetween('created_at', [$date->startOfDay(), $date->copy()->endOfDay()])->count(),
                    'group' => Group::whereBetween('created_at', [$date->startOfDay(), $date->copy()->endOfDay()])->count(),
                ];
            });
            $users[$key] = $data['user'];
            $pages[$key] = $data['page'];
            $posts[$key] = $data['post'];            
            $reports[$key] = $data['report'];
            $groups[$key] = $data['group'];
        }

        return response()->json([
            'user' => ['label' => array_keys($users), 'data' => array_values($users)],
            'page' => ['label' => array_keys($pages), 'data' => array_values($pages)],
            'post' => ['label' => array_keys($posts), 'data' => array_values($posts)],
            'report' => ['label' => array_keys($reports), 'data' => array_values($reports)],
            'group' => ['label' => array_keys($groups), 'data' => array_values($groups)],
        ]);
    }

    public function change_language(AdminChangeLanguageValidate $request)
    {
        $this->userRepository->store_language($request->key, $request->user());

        return redirect()->back();
    }
}
