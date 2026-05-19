<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Install;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Models\Setting;
use Packages\ShaunSocial\Core\Models\User;

class AdminController extends Controller
{
    /**
     * Display the create admin user page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('shaun_core::install.admin');
    }

    /**
     * Processes the newly saved user admin
     *
     * @param  Request  $request
     * @param  Redirector  $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, Redirector $redirect)
    {
        $rules = config('shaun_core_install.validation.admin.rules');
        $messages = [];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $redirect->route('install.admin')->withInput()->withErrors($validator->errors());
        }

        $timezoneSetting = Setting::where('key','site.timezone')->first();

        $results = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => config('shaun_core.role.id.root'),
            'user_name' => 'admin',
            'already_setup_login' => true,
            'is_active' => true,
            'timezone' => $timezoneSetting->value,
            'email_verified' => true,
            'phone_verified' => true,
            'darkmode' => 'auto',
            'video_auto_play' => true
        ]);

        return $redirect->route('install.final')
            ->with(['results' => $results, 'message' => __('Application has been successfully installed.')]);
    }

    /**
     * Validate form data
     *
     * @return string
     */
    public function validate(Request $request)
    {
        $rules = config('shaun_core_install.validation.admin.rules');
        $messages = [];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
    }
}
