<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;

class DocumentsLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'document.index'
        ]);

        return $this->renderData($request);
    }
}
