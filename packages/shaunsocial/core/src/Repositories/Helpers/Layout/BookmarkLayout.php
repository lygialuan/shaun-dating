<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;

class BookmarkLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'bookmark.index'
        ]);

        return $this->renderData($request);
    }
}
