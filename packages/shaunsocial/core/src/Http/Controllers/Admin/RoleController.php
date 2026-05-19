<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\PermissionGroup;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('is.supper.admin');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Roles'),
            ],
        ];
        $title = __('Roles');
        $roles = Role::all();

        return view('shaun_core::admin.role.index', compact('breadcrumbs', 'title', 'roles'));
    }

    public function create($id = null)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Roles'),
                'route' => 'admin.role.index',
            ],
        ];
        if (empty($id)) {
            $role = new Role();
            $breadcrumbs[] = [
                'title' => __('Create'),
            ];
        } else {
            $role = Role::findOrFail($id);

            $breadcrumbs[] = [
                'title' => __('Edit'),
            ];
        }

        $title = empty($id) ? __('Create') : __('Edit');
        $roles = Role::all();
        $roles = $roles->filter(function ($value, $key) {
            return ! in_array($value->id, [config('shaun_core.role.id.root'), config('shaun_core.role.id.guest')]);
        });
        return view('shaun_core::admin.role.create', compact('roles', 'title', 'role', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $data = $request->except('id', '_token');

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

        $request->mergeIfMissing([
            'is_moderator' => 0,
        ]);
        $data['is_moderator'] = $request->is_moderator;

        if ($request->id) {
            $role = Role::findOrFail($request->id);
            $role->update($data);
        } else {
            $inherit = $request->inherit;

            $role = Role::create($data);

            $permissionCloneValues = RolePermission::where('role_id', $inherit)->get();

            $permissionValues = $permissionCloneValues->map(function ($item, $key) use ($role) {
                $item->role_id = $role->id;

                return $item->toArray();
            })->toArray();

            if (count($permissionValues)) {
                RolePermission::insert($permissionValues);
            }
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Role has been successfully updated.') : __('Role has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function permission($id)
    {
        $role = Role::findOrFail($id);

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Roles'),
                'route' => 'admin.role.index',
            ],
            [
                'title' => __('Permissions'),
            ],
        ];

        $title = $role->name;
        $permissionValues = $role->permissions->keyBy('permission_id');
        $groups = PermissionGroup::orderBy('order', 'ASC')->get();
        
        return view('shaun_core::admin.role.permission', compact('permissionValues', 'groups', 'title', 'role', 'breadcrumbs'));
    }

    public function store_permission(Request $request)
    {
        $data = $request->except('id', '_token');

        $role = Role::findOrFail($request->id);
        RolePermission::where('role_id', $request->id)->delete();

        $permissions = Permission::all()->keyBy('id');
        $permissionValues = [];
        foreach ($data as $key => $value) {
            if (isset($permissions[$key])) {
                $permissionValues[] = [
                    'role_id' => $role->id,
                    'permission_id' => $key,
                    'value' => $value ? $value : '',
                ];
            }
        }
        if (count($permissionValues)) {
            RolePermission::insert($permissionValues);
        }

        Cache::forget('permissions_role_'.$role->id);

        return redirect()->route('admin.role.index')->with([
            'admin_message_success' => $request->id ? __('Role has been successfully updated.') : __('Role has been created.'),
        ]);
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);

        if (! $role->canDelete()) {
            abort(400);
        }

        $role->doDeleted();

        if ($role->is_default) {
            Role::where('id', config('shaun_core.role.id.member_default'))->update(['is_default' => 1]);
        }

        return redirect()->route('admin.role.index')->with([
            'admin_message_success' => __('Role has been deleted.'),
        ]);
    }

    public function translate_permission($package = 'shaun_core')
    {
        $permissionGroups = PermissionGroup::all();
        foreach ($permissionGroups as $group) {
            if ($group->package == $package) {
                echo "__('".$group->name."');\n";
            }            
        }

        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            if ($permission->getGroup()->package != $package) {
                continue;
            }

            echo "__('".$permission->name."');\n";
            if ($permission->description) {  
                echo "__('".$permission->description."');\n";
            }
        }

        die();
    }

    public function store_default(Request $request)
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

        $role = Role::findOrFail($request->id);

        Role::query()->update(['is_default' => 0]);
        $role->update(['is_default' => 1]);

        Cache::forget('role_default');

        return response()->json([
            'status' => true,
        ]);
    }
}
