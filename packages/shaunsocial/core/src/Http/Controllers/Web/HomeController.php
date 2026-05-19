<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Web;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Repositories\Helpers\Layout\HomeLayout;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return app(HomeLayout::class)->render($request);
    }
}
