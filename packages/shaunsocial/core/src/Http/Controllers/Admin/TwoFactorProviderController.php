<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Models\User;

class TwoFactorProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.two_factor_provider.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Two-Factor Providers'),
            ],
        ];
        $title = __('Two-Factor Providers');
        $providers = TwoFactorProvider::all();

        return view('shaun_core::admin.two_factor_provider.index', compact('breadcrumbs', 'title', 'providers'));
    }

    public function edit($id)
    {
        $provider = TwoFactorProvider::findOrFail($id);

        return view('shaun_core::admin.two_factor_provider.edit', compact('provider'));
    }

    public function store(Request $request)
    {
        $rules = [
            'id' => 'required',
            'is_active' => 'boolean',
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

        $request->mergeIfMissing([
            'is_active' => false
        ]);
        $data = $request->except('id', '_token');

        $provider = TwoFactorProvider::findOrFail($request->id);
        $provider->update($data);

        $request->session()->flash('admin_message_success', __('Provider has been successfully updated.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function remove_user($id)
    {
        $user = User::findOrFail($id);
        if (!$user->getTwoFactor() || ! $user->canActionOnAdminPanel(auth()->user())) {
            abort(400);
        }

        $user->getTwoFactor()->delete();

        return redirect()->back()->with([
            'admin_message_success' => __('The two-factor of user have been removed.'),
        ]);
    }
}
