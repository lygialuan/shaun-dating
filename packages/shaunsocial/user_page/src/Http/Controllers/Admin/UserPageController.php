<?php

namespace Packages\ShaunSocial\UserPage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\UserPage\Http\Requests\AdminStoreEditPageValidate;
use Packages\ShaunSocial\UserPage\Models\UserPageAdmin;

class UserPageController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->middleware('has.permission:admin.user_page.manage');
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Manage Profiles'),
            ],
        ];
        $title = __('Manage Profiles');
        $builder = User::where('is_page', 1)->orderBy('id','desc');

        $name = $request->query('name');
        if ($name) {
            $builder->where(function ($query) use ($name){
                $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('user_name', 'LIKE', '%'.$name.'%');
            });
        }

        $ip = $request->query('ip');
        if ($ip) {
            $builder->where('ip','LIKE', '%'.$ip.'%');
        }

        $statusArray = [
            '' => __('All status'),
            '1' => __('Active'),
            '0' => __('InActive')
        ];

        $status = $request->query('status', '');
        if (! in_array($status, array_keys($statusArray))) {
            $status = '';
        }

        if ($status !== '') {
            $builder->where('is_active', $status);
        }

        $verifesArray = [
            '' => __('All'),
            UserVerifyStatus::OK->value => __('Verified'),
            UserVerifyStatus::NONE->value => __('Unverified')
        ];
        $verify = $request->query('verify', '');
        if (! in_array($verify, array_keys($verifesArray))) {
            $verify = '';
        }

        if ($verify) {
            if ($verify == UserVerifyStatus::OK->value) {
                $builder->where('verify_status', UserVerifyStatus::OK); 
            } else {
                $builder->where('verify_status', '!=', UserVerifyStatus::OK); 
            }
        }

        $roles = Role::getMemberRoles()->pluck('name', 'id')->toArray();
        
        $roleId = $request->query('role_id', '');
        if (! in_array($roleId, array_keys($roles))) {
            $roleId = '';
        }

        if ($roleId !== '') {
            $builder->where('role_id', $roleId);
        }

        $pages = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_user_page::admin.user_page.index', compact('breadcrumbs', 'title', 'pages', 'name', 'status', 'roleId', 'statusArray', 'roles', 'ip', 'verify', 'verifesArray'));
    }

    public function edit($id)
    {
        $page = User::findOrFail($id);

        if (! $page->isPage()) {
            abort(400);
        }
        
        $title = __('Edit');
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Profiles'),
                'route' => 'admin.user_page.index',
            ],
            [
                'title' => $title,
            ],
        ];

        $roles = Role::getMemberRoles();
        return view('shaun_user_page::admin.user_page.edit', compact('breadcrumbs', 'title', 'page', 'roles'));
    }

    public function admin_manage($id)
    {
        $page = User::findOrFail($id);

        if (! $page->isPage()) {
            abort(400);
        }

        $title = __('Admin Manage');
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Profiles'),
                'route' => 'admin.user_page.index',
            ],
            [
                'title' => $title,
            ],
        ];

        $admins = UserPageAdmin::where('user_page_id', $id)->get();
        return view('shaun_user_page::admin.user_page.admin_manage', compact('breadcrumbs', 'title', 'page', 'admins'));
    }

    public function store_edit(AdminStoreEditPageValidate $request)
    {
        $request->mergeIfMissing([
            'is_active' => false,
        ]);

        $data = $request->except('id', '_token');

        $user = User::findOrFail($request->id);
        $viewer = $request->user();
        if (! $user->canActionOnAdminPanel($viewer)) {
            abort(400);
        }

        $activeCurrent = $user->is_active;
        if ($request->id == config('shaun_core.core.user_root_id') && isset($data['role_id'])) {
            unset($data['role_id']);
        }

        if (isset($data['role_id'])) {
            $role = Role::findOrFail($data['role_id']);
            if (!$role->isMember()) {
                abort(400);
            }
        }

        $user->update($data);

        if ($activeCurrent != $data['is_active']) {
            if ($data['is_active']) {
                $this->userRepository->do_active($user);
            } else {
                $this->userRepository->do_inactive($user);
            }
        }

        return redirect()->route('admin.user_page.index')->with([
            'admin_message_success' => __('Page has been successfully updated.'),
        ]);
    }

    public function store_manage(Request $request)
    {
        $viewer = $request->user();

        $ids = $request->get('ids');        
        if (! is_array($ids)) {
            abort(404);
        }
        $message = '';
        $action = $request->get('action');
        
        switch ($action) {
            case 'delete':
                foreach ($ids as $id) {
                    $page = User::findByField('id', $id);
                    if ($page && $page->isPage() && ! $page->isRoot() && $page->canActionOnAdminPanel($viewer)) {
                        $this->userRepository->delete($page);
                    }
                }
                $message = __('The selected page(s) have been deleted.');
                break;
            case 'active':
                foreach ($ids as $id) {
                    $page = User::findByField('id', $id);
                    if ($page && $page->isPage() && $page->canActionOnAdminPanel($viewer)) {
                        $page->update([
                            'is_active' => true
                        ]);
                    }
                }
    
                $message = __('The selected page(s) have been activated successfully.');
                break;
            default:
                abort(404);
                break;
        }
        
        return redirect()->back()->with([
            'admin_message_success' => $message,
        ]);
    }
    
    public function remove_login_all_devices(Request $request, $id)
    {
        $page = User::findOrFail($id);

        if (! $page->isPage()) {
            abort(400);
        }

        $page->doRemoveLoginAllDevices();

        return redirect()->back()->with([
            'admin_message_success' => __('Log out successfully.'),
        ]);
    }
}
