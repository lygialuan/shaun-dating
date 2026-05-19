<?php

namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;

class ExploreLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'explore.index'
            // 'router' => 'explore_page.index'
        ]);

        return $this->renderData($request);
    }
}
