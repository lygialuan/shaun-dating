<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Theme as ThemeModel;
use Packages\ShaunSocial\Core\Support\Facades\Theme;

class ThemeControler extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.theme.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.theme.index',
            ],
            [
                'title' => __('Themes'),
            ],
        ];
        $title = __('Themes');
        $themes = ThemeModel::all();

        return view('shaun_core::admin.theme.index', compact('breadcrumbs', 'title', 'themes'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $theme = new ThemeModel();
        } else {
            $theme = ThemeModel::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_core::admin.theme.create', compact('title', 'theme'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }
        $data = $request->except('id', '_token');

        if ($request->id) {
            $theme = ThemeModel::findOrFail($request->id);

            $theme->update($data);
        } else {
            $theme = ThemeModel::create($data);
            $theme->update([
                'settings' => json_encode(getThemeSettingDefault()),
                'settings_dark' => json_encode(getThemeSettingDarkDefault())
            ]);
            Theme::build($theme);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Theme has been successfully updated.') : __('Theme has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $theme = ThemeModel::findOrFail($id);

        $theme->delete();

        return redirect()->route('admin.theme.index')->with([
            'admin_message_success' => __('Theme has been deleted.'),
        ]);
    }

    public function setting($type, $id)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Themes'),
                'route' => 'admin.theme.index',
            ]
        ];

        $theme = ThemeModel::findOrFail($id);
        if ($type != 'dark') {
            $settings = Theme::getAdminSettings($theme->getSettings());
            $breadcrumbs[] = [
                'title' => __('Themes Settings Light'),
            ];
        } else {
            $settings = Theme::getAdminSettings($theme->getSettingsDark());
            $breadcrumbs[] = [
                'title' => __('Themes Settings Dark'),
            ];
        }

        $title = $theme->name;

        return view('shaun_core::admin.theme.setting', compact('breadcrumbs', 'title', 'settings', 'id', 'type'));
    }

    public function store_setting(Request $request)
    {
        $theme = ThemeModel::findOrFail($request->id);
        $type = $request->type;
        $data = $request->except('_method', '_token', 'id', 'type');
        if ($type != 'dark') {
            $theme->update([
                'settings' => json_encode($data)
            ]);
        } else {
            $theme->update([
                'settings_dark' => json_encode($data)
            ]);
        }
        Theme::build($theme);

        return redirect()->back()->with([
            'admin_message_success' => __('Theme settings have been successfully saved.'),
        ]);
    }

    public function store_reset_setting(Request $request)
    {
        $theme = ThemeModel::findOrFail($request->id);
        $type = $request->type;

        if ($type != 'dark') {
            $theme->update([
                'settings' => ''
            ]);
        } else {
            $theme->update([
                'settings_dark' => ''
            ]);
        }
        Theme::build($theme);

        return redirect()->back()->with([
            'admin_message_success' => __('Theme settings have been successfully reset.'),
        ]);
    }

    public function store_active(Request $request)
    {
        $rules = [
            'id' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $theme = ThemeModel::findOrFail($request->id);

        ThemeModel::query()->update(['is_active' => 0]);
        $theme->update(['is_active' => 1]);
        ThemeModel::clearCache();

        return response()->json([
            'status' => true,
        ]);
    }
}
