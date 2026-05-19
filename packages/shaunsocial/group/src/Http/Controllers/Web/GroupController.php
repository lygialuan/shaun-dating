<?php


namespace Packages\ShaunSocial\Group\Http\Controllers\Web;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Group\Repositories\Helpers\Layout\GroupProfileLayout;

class GroupController extends Controller
{
    public function profile(Request $request)
    {        
        return app(GroupProfileLayout::class)->render($request);
    }
}