<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Menu;
use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Validation\FileValidation;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.menu.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Menus'),
            ],
        ];
        $title = __('Menus');

        $menus = Menu::orderBy('id')->get();
        $menuItems = $menus->pluck('name', 'id')->map(function ($item, $key) {
            return MenuItem::getItem($key);
        });

        return view('shaun_core::admin.menu.index', compact('breadcrumbs', 'title', 'menus', 'menuItems'));
    }

    public function create_item($menu_id, $id = null)
    {
        $menu = Menu::findOrFail($menu_id);

        if (empty($id)) {
            $menuItem = new MenuItem();
            $menuItem->is_active = true;
        } else {
            $menuItem = MenuItem::findOrFail($id);
        }

        $roles = Role::all();

        $menuItemParents = MenuItem::where('menu_id', $menu_id)->where('id', '!=', $menuItem->id)->where('is_header', true)->where('parent_id', 0)->get();

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_core::admin.menu.create_item', compact('title', 'roles', 'menuItemParents', 'menu', 'menuItem'));
    }

    public function store_item(Request $request)
    {
        $request->mergeIfMissing([
            'is_new_tab' => 0,
            'is_active' => 0,
            'is_header' => 0,
            'is_core' => 0,
            'role_access' => [],
        ]);
        
        $rules = [
            'name' => 'required|max:255',
            'icon' => new FileValidation('svg')
        ];

        if (! $request->is_header && ! $request->is_core) {
            $rules['url'] = 'required';
        }

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
                'url.required' => __('The url is required.'),
                'icon.uploaded' => __('The icon is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
            ]
        );
        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $data = $request->except('id', '_token');
        $data['role_access'] = json_encode($data['role_access']);

        if ($request->id) {
            $menuItem = MenuItem::findOrFail($request->id);
            $menuItem->update($data);
        } else {
            $menuItem = MenuItem::create($data);
        }

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');

            $storageFile = File::store($file, [
                'parent_id' => $menuItem->id,
                'parent_type' => 'menu_item',
                'extension' => $file->getClientOriginalExtension(),
                'name' => $file->getClientOriginalName()
            ]);
            $menuItem->update(['icon_file_id' => $storageFile->id]);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Menu has been successfully updated.') : __('Menu has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete_item($id)
    {
        $menuItem = MenuItem::findOrFail($id);

        if ($menuItem->canDelete()) {
            $menuItem->delete();
        }        

        return redirect()->route('admin.menu.index', $menuItem->menu_id)->with([
            'admin_message_success' => __('Menu has been deleted.'),
        ]);
    }

    public function store_order(Request $request)
    {
        $rules = [
            'orders' => 'required',
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

        $orders = $request->orders;
        foreach ($orders as $order => $id) {
            $menuItem = MenuItem::find($id);
            if (! $menuItem) {
                continue;
            }
            $menuItem->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }
}
