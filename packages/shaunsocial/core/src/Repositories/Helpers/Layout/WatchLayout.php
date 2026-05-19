<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;

class WatchLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'watch.index'
        ]);

        return $this->renderData($request);
    }
}
