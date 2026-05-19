<?php

namespace Packages\ShaunSocial\UserPage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\User;

class UserPageVerifyController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.user_page.manage_verifies');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Profile Verification Requests'),
            ],
        ];
        $title = __('Profile Verification Requests');
        
        $builder = User::where('verify_status', UserVerifyStatus::SENT)->where('is_page', true)->orderBy('verify_status_at','desc');

        $name = $request->query('name');
        if ($name) {
            $builder->where(function ($query) use ($name){
                $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('user_name', 'LIKE', '%'.$name.'%');
            });
        }

        $pages = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_user_page::admin.verify.index', compact('breadcrumbs', 'title', 'pages', 'name'));
    }

    public function store_verify(Request $request)
    {
        $page = User::findOrFail($request->id);
        if ($page->verify_status == UserVerifyStatus::OK || ! $page->isPage()) {
            abort(403);
        }

        $page->doVerify();

        return redirect()->back()->with([
            'admin_message_success' =>  __('This page has been successfully verified.'),
        ]);
    }

    public function reject(Request $request)
    {
        $page = User::findOrFail($request->id);
        if ($page->verify_status != UserVerifyStatus::SENT || ! $page->isPage()) {
            abort(403);
        }

        return view('shaun_user_page::admin.verify.reject', compact('page'));
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
        
        $page = User::findOrFail($request->id);
        if ($page->verify_status != UserVerifyStatus::SENT || ! $page->isPage()) {
            abort(403);
        }

        $reason = $request->post('reason');

        $page->doRejectVerify($request->user(), $reason);

        $request->session()->flash('admin_message_success', __('This request has been successfully rejected.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function store_unverify(Request $request)
    {
        $page = User::findOrFail($request->id);
        if ($page->verify_status != UserVerifyStatus::OK || ! $page->isPage()) {
            abort(403);
        }

        $page->doUnVerify();

        return redirect()->back()->with([
            'admin_message_success' =>  __('This page has been successfully unverified.'),
        ]);
    }
}
