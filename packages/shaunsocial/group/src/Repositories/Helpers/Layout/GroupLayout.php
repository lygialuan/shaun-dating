<?php


namespace Packages\ShaunSocial\Group\Repositories\Helpers\Layout;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Repositories\Helpers\Layout\BaseLayout;

class GroupLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'group.index'
        ]);

        return $this->renderData($request);
    }
}
