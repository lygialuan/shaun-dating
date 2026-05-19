<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Web;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Repositories\Helpers\Layout\UserProfileLayout;

class UserController extends Controller
{
    public function profile(Request $request)
    {        
        return app(UserProfileLayout::class)->render($request);
    }

    public function signup(Request $request)
    {
        return view('shaun_core::app', ['title' => __('Signup')]);
    }
}
