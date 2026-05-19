<?php

namespace Packages\ShaunSocial\Group\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Models\PostHome;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Group\Enum\GroupStatus;
use Packages\ShaunSocial\Group\Enum\GroupType;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Notification\GroupActiveNotification;
use Packages\ShaunSocial\Group\Notification\GroupApproveNotification;
use Packages\ShaunSocial\Group\Notification\GroupDisableNotification;
use Packages\ShaunSocial\Group\Repositories\Api\GroupRepository;

class GroupController extends Controller
{
    protected $groupRepository = null;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->middleware('has.permission:admin.group.manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Groups'),
            ],
        ];
        $title = __('Groups');
        $builder = Group::orderBy('id','desc');

        $name = $request->query('name');
        if ($name) {
            $builder->where(function ($query) use ($name){
                $query->where('name', 'LIKE', '%'.$name.'%');
            });
        }

        $statusArray = GroupStatus::getAll();

        $status = $request->query('status', '');
        if (! in_array($status, array_keys($statusArray))) {
            $status = '';
        }
        if ($status) {
            $builder->where('status', $status);
        }

        $popularArray = [
            'all' => __('All'),
            'yes' => __('Popular'),
            'no' => __('Not popular')
        ];

        $popular = $request->query('popular', 'all');
        if (! in_array($popular, array_keys($popularArray))) {
            $popular = 'all';
        }
        if ($popular) {
            switch ($popular) {
                case 'yes':
                    $builder->where('is_popular', true);
                    break;
                case 'no':
                    $builder->where('is_popular', false);
                    break;
            }
        }

        $groups = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_group::admin.group.index', compact('breadcrumbs', 'title', 'groups', 'name', 'status', 'statusArray', 'popular', 'popularArray'));
    }

    public function delete($id)
    {
        $group = Group::findOrFail($id);
        $this->groupRepository->delete($id);

        return redirect()->back()->with([
            'admin_message_success' => __('Group has been deleted.'),
        ]);
    }

    public function disable($id)
    {
        $group = Group::findOrFail($id);
        if (! $group->canDisable()) {
            abort(404);
        }
        $group->update([
            'status' => GroupStatus::DISABLE,
            'datetime_change_status' => now()
        ]);

        PostHome::where('source_id', $id)->where('source_type', 'groups')->update([
            'source_privacy' => GroupType::HIDDEN
        ]);

        //send notify
        $member = GroupMember::getOwner($group->id);
        $user = $member->getUser();
        Notification::send($user, $user, GroupDisableNotification::class, $group, ['is_system' => true], 'shaun_group', false, true, true);

        return redirect()->back()->with([
            'admin_message_success' => __('Group has been disabled.'),
        ]);
    }

    public function approve($id)
    {
        $group = Group::findOrFail($id);
        if (! $group->canApprove()) {
            abort(404);
        }
        $group->update([
            'status' => GroupStatus::ACTIVE,
            'datetime_change_status' => now()
        ]);

        //send notify
        $member = GroupMember::getOwner($group->id);
        $user = $member->getUser();
        Notification::send($user, $user, GroupApproveNotification::class, $group, ['is_system' => true], 'shaun_group', false, true, true);

        return redirect()->back()->with([
            'admin_message_success' => __('Group has been approved.'),
        ]);
    }

    public function active($id)
    {
        $group = Group::findOrFail($id);
        if (! $group->canActive()) {
            abort(404);
        }

        $group->update([
            'status' => GroupStatus::ACTIVE,
            'datetime_change_status' => now()
        ]);

        PostHome::where('source_id', $id)->where('source_type', 'groups')->update([
            'source_privacy' => $group->type->value
        ]);

        //send notify
        $member = GroupMember::getOwner($group->id);
        $user = $member->getUser();
        Notification::send($user, $user, GroupActiveNotification::class, $group, ['is_system' => true], 'shaun_group', false, true, true);

        return redirect()->back()->with([
            'admin_message_success' => __('Group has been activated.'),
        ]);
    }

    public function admin_manage($id)
    {
        $group = Group::findOrFail($id);

        $title = __('Admin Manage');
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Groups'),
                'route' => 'admin.group.index',
            ],
            [
                'title' => $title,
            ],
        ];

        $admins = GroupMember::where('group_id', $id)->get();
        return view('shaun_group::admin.group.admin_manage', compact('breadcrumbs', 'title', 'group', 'admins'));
    }

    public function store_manage(Request $request)
    {
        $ids = $request->get('ids');        
        if (! is_array($ids)) {
            abort(404);
        }
        $message = '';
        $action = $request->get('action');
        
        switch ($action) {
            case 'delete':
                foreach ($ids as $id) {
                    $group = Group::findByField('id', $id);
                    if ($group) {
                        $this->groupRepository->delete($id);
                    }
                }
                $message = __('The selected group(s) have been deleted.');
                break;
            default:
                abort(404);
                break;
        }
        
        return redirect()->back()->with([
            'admin_message_success' => $message,
        ]);
    }

    public function popular($id)
    {
        $group = Group::findOrFail($id);
        if ($group->is_popular) {
            abort(404);
        }

        $group->update(['is_popular' => true]);

        return redirect()->back()->with([
            'admin_message_success' => __('Set popular successfully.'),
        ]);
    }

    public function remove_popular($id)
    {
        $group = Group::findOrFail($id);
        if (! $group->is_popular) {
            abort(404);
        }
        $group->update(['is_popular' => false]);

        return redirect()->back()->with([
            'admin_message_success' => __('Remove popular successfully.'),
        ]);
    }
}
