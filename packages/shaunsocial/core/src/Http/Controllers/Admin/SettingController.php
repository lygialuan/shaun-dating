<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Setting;
use Packages\ShaunSocial\Core\Models\SettingGroup;
use Packages\ShaunSocial\Core\Models\SettingGroupSub;
use Packages\ShaunSocial\Core\Repositories\SettingRepository;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    protected $settingRepository;

    public function __construct(SettingRepository $settingRepository)
    {
        $routerName = Route::getCurrentRoute()->getName();
        switch ($routerName) {
            case 'admin.setting.site':
                $this->middleware('has.permission:admin.setting.site');
                break;
            case 'admin.setting.general':
                $this->middleware('has.permission:admin.setting.general');
                break;
            case 'admin.setting.mobile_general':
                $this->middleware('has.permission:admin.setting.mobile_general');
                break;
            default:
                $this->middleware('has.permission:admin.setting.site|admin.setting.general|admin.setting.mobile_general');
                break;
        }

        $this->settingRepository = $settingRepository;
    }

    public function site()
    {
        return $this->index('site');
    }

    public function general()
    {
        return $this->index('general');
    }

    public function mobile_general()
    {
        return $this->index('mobile_general');
    }

    protected function index($key = null)
    {
        $group = SettingGroup::where('key', $key)->first();
        if (! $group) {
            abort(404);
        }

        $title = __($group->name);

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => $title,
            ],
        ];

        return view('shaun_core::admin.setting.index', compact('breadcrumbs', 'title', 'group'));
    }

    public function store(Request $request)
    {
        try {
            $setting = $this->settingRepository->saveSingle($request);
            if ($setting->type == 'image') {
                $request->session()->flash('admin_message_success', __('Setting has been successfully updated.'));
            }
        } catch (Exception $e) {
            $request->session()->flash('admin_message_error', $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete_image($id)
    {
        $setting = Setting::findOrFail($id);

        if (isset($setting->id)) {
            if ($setting->type == 'image') {
                $setting->value = '';
                $setting->save();
            }
        }
        clearSettingCache();
        
        return back()->with([
            'admin_message_success' => __('Successfully removed :name value.', ['name' => __($setting->name)]),
        ]);
    }

    public function suggest(Request $request, $text)
    {
        $viewer = $request->user();

        if (! $text) {
            return [];
        }

        $settings = Cache::remember('settings_search', config('shaun_core.cache.time.model_query'), function () {
            return Setting::where('hidden', false)->get();
        });

        $settings = $settings->filter(function ($setting, $key) use ($text, $viewer) {
            $name = __($setting->name);
            if (str_contains(Str::lower($name), Str::lower($text))) {
                $group = $setting->getGroup();
                switch ($group->key) {
                    case 'site' :
                        return $viewer->hasPermission('admin.setting.site');
                        break;
                    case 'general' :
                        return $viewer->hasPermission('admin.setting.site');
                        break;
                }
            }

            return false;
        });

        $settings = $settings->map(function ($setting, $key) {
            $href = '';
            $group = $setting->getGroup();
            switch ($group->key) {
                case 'site' :
                    $href = route('admin.setting.site');
                    break;
                case 'general' :
                    $href = route('admin.setting.general');
                    break;
            }

            return [
                'href' => $href.'#setting_id_'.$setting->id,
                'name' => __($setting->name),
                'des' => __('Site Settings'). ' > '. __($group->name). ' > '. __($setting->getGroupSub()->name),
            ];
        });
    
        $settings = $settings->sortBy([
            'name', 'asc',
        ]);

        $limit = 5;

        if (count($settings) > $limit) {
            $settings = $settings->slice(0, $limit);
        }

        return response()->json(
            $settings->values()->toArray()
        );
    }

    public function check_fmpeg()
    {
        $ffmpeg = getFFMpeg();

        if (! $ffmpeg) {
            return response()->json([
                'status' => false,
                'message' => __('FFMPEG is not installed or incorrect path.'),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __('FFMPEG is installed'),
        ]);
    }

    public function translate($package = 'shaun_core')
    {
        $settingGroups = SettingGroup::all();
        foreach ($settingGroups as $group) {
            if ($package == 'shaun_core') {
                echo "__('".$group->name."');\n";
            }            
        }

        $settingGroupSubs = SettingGroupSub::all();
        foreach ($settingGroupSubs as $groupSub) {
            if ($groupSub->package == $package) {
                echo "__('".$groupSub->name."');\n";
            }            
        }

        $settings = Setting::all();
        foreach ($settings as $setting) {
            if ($setting->group_sub_id) {
                if ($setting->getGroupSub()->package != $package) {
                    continue;
                }
            } elseif ($package != 'shaun_core' ) {
                continue;
            }

            if ($setting->name) {
                echo "__('".$setting->name."');\n";
            }
            
            if ($setting->description) {  
                $key = addslashes($setting->description);
                echo "__('".$key."');\n";
            }

            if ($setting->type == 'select' || $setting->type == 'radio') {
                $params = $setting->getParams();
                foreach ($params as $text) {
                    echo "__('".$text."');\n";
                }
            }
        }

        die();
    }
}
