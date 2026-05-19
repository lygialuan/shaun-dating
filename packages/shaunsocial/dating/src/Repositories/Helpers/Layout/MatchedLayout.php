<?php

namespace Packages\ShaunSocial\Dating\Repositories\Helpers\Layout;

use Illuminate\Http\Request;

class MatchedLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'matched.index'
        ]);

        return $this->renderData($request);
    }
}
