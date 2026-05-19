<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;

class MediaLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'media.index'
        ]);

        return $this->renderData($request);
    }
}
