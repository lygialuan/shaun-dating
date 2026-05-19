<?php

namespace Packages\ShaunSocial\UserVerify\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\User;

class UserVerifyController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.user_verify.request_manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Verification Requests'),
            ],
        ];
        $title = __('Verification Requests');
        
        $builder = User::where('verify_status', UserVerifyStatus::SENT)->where('is_page', false)->orderBy('verify_status_at','desc');

        $name = $request->query('name');
        if ($name) {
            $builder->where(function ($query) use ($name){
                $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('user_name', 'LIKE', '%'.$name.'%');
            });
        }

        $users = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_user_verify::admin.user_verify.index', compact('breadcrumbs', 'title', 'users', 'name'));
    }

    public function store_verify(Request $request)
    {
        $user = User::findOrFail($request->id);
        if ($user->verify_status == UserVerifyStatus::OK || $user->isPage()) {
            abort(403);
        }

        $user->doVerify();

        return redirect()->back()->with([
            'admin_message_success' =>  __('This user has been successfully verified.'),
        ]);
    }

    public function reject(Request $request)
    {
        $user = User::findOrFail($request->id);
        if ($user->verify_status != UserVerifyStatus::SENT || $user->isPage()) {
            abort(403);
        }

        return view('shaun_user_verify::admin.user_verify.reject', compact('user'));
    }

    public function store_reject(Request $request)
    {
        $rules = [
            'id' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'id.required' => __('The id is required.'),
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $user = User::findOrFail($request->id);
        if ($user->verify_status != UserVerifyStatus::SENT || $user->isPage()) {
            abort(403);
        }

        $reason = $request->post('reason');

        $user->doRejectVerify($request->user(), $reason);

        $request->session()->flash('admin_message_success', __('This request has been successfully rejected.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function store_unverify(Request $request)
    {
        $user = User::findOrFail($request->id);
        if ($user->verify_status != UserVerifyStatus::OK || $user->isPage()) {
            abort(403);
        }

        $user->doUnVerify();

        return redirect()->back()->with([
            'admin_message_success' =>  __('This user has been successfully unverify.'),
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
            case 'verify':
                foreach ($ids as $id) {
                    $user = User::findByField('id', $id);
                    if ($user && ! $user->isRoot() && $user->canActionOnAdminPanel($viewer)) {
                        $user->doVerify();
                    }
                }
                $message = __('The selected request(s) have been verified.');
                break;
            case 'reject':
                foreach ($ids as $id) {
                    $user = User::findByField('id', $id);
                    if ($user && ! $user->isRoot() && $user->canActionOnAdminPanel($viewer)) {
                        $user->doRejectVerify($request->user());
                    }
                }
    
                $message = __('The selected request(s) have been rejected successfully.');
                break;
            default:
                abort(404);
                break;
        }
        
        return redirect()->back()->with([
            'admin_message_success' => $message,
        ]);
    }
}
