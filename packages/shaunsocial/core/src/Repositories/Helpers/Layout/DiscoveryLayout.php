<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;

class DiscoveryLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'discovery.index'
        ]);

        return $this->renderData($request);
    }
}
