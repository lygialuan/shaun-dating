<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Web;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Repositories\Helpers\Layout\PageLayout;

class PageController extends Controller
{
    public function detail(Request $request)
    {
        return app(PageLayout::class)->render($request);
    }
}
