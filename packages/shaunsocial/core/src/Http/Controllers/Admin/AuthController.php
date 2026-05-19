<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Http\Requests\Auth\AdminLoginValidate;
use Packages\ShaunSocial\Core\Models\Role;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $redirect = $request->query('redirect', '');
        return view('shaun_core::admin.auth.index', ['title' => __('Admin Login'), 'redirect' => $redirect]);
    }

    public function login(AdminLoginValidate $request)
    {
        $data = $request->only(['email', 'password']);

        $roles = Role::where('is_moderator', true)->orWhere('is_supper_admin', true)->withoutGlobalScopes()->pluck('id')->all();
        $data['role_id'] = $roles;
        if (! Auth::guard('admin')->attempt($data)) {
            return redirect()->route('admin.auth.index')->withInput()->withErrors([
                'errors' => [
                    __('Your email or password was incorrect.'),
                ],
            ]);
        }
        if ($request->redirect) {
            $redirect = base64_decode($request->redirect);
            if (strpos($redirect, setting('site.url')) !== false) {
                return redirect(base64_decode($request->redirect));
            }
        }

        return redirect()->route('admin.dashboard.index');        
    }

    public function logout()
    {
        Session::flush();

        Auth::logout();

        return redirect()->route('admin.auth.index');
    }
}
