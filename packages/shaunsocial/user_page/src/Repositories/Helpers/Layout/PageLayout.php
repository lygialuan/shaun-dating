<?php


namespace Packages\ShaunSocial\UserPage\Repositories\Helpers\Layout;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Repositories\Helpers\Layout\BaseLayout;

class PageLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'page.index'
        ]);

        return $this->renderData($request);
    }
}
