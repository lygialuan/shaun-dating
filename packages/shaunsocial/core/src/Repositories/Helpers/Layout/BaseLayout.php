<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Models\LayoutContent;
use Packages\ShaunSocial\Core\Models\LayoutPage;
use Packages\ShaunSocial\Core\Traits\ApiResponser;
use Packages\ShaunSocial\Core\Traits\Utility;

class BaseLayout
{
    use ApiResponser, Utility;

    public function getLayoutSeo($layoutPage)
    {
        return [
            'title' => $layoutPage->getTranslatedAttributeValue('title'),
            'description' => $layoutPage->meta_description,
            'keywords' => $layoutPage->meta_keywords,
        ];
    }

    public function getLayoutPage($router, $pageId = null)
    {
        if ($pageId) {
            $layoutPage = LayoutPage::findByField('page_id', $pageId);            
        } else {
            $layoutPage = LayoutPage::findByField('router', $router);
        }

        return $layoutPage;
    }

    public function renderNotFound($message)
    {
        $route = Route::current();
        $middlewares = $route->gatherMiddleware();

        if (in_array('web', $middlewares)) {
            return view('shaun_core::app', []);
        }

        return $this->errorNotFound($message);
    }

    public function renderData(Request $request, $pageId = null, $data = [])
    {
        $route = Route::current();
        $middlewares = $route->gatherMiddleware();
        
        $router = $request->router;
        $layoutPage = $this->getLayoutPage($router, $pageId);
        $seoData = $this->getLayoutSeo($layoutPage);
        $data = array_merge($seoData, $data);
        if (in_array('web', $middlewares)) {
            return view('shaun_core::app', $data);
        }
        
        $viewType = $request->view_type;
        $viewTypes = LayoutContent::getViewTypes();
        if (! in_array($viewType, array_keys($viewTypes))) {
            $viewType = 'desktop';
        }

        $data['contents'] = $this->getLayoutContent($request, $layoutPage->id, $viewType);
        return $this->successResponse($data);
    }
}
