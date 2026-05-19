<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Models\Page;

class PageLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $route = Route::current();
        $middlewares = $route->gatherMiddleware();
        
        $page = Page::findByField('slug', $request->slug);
        if (! $page) {
            return $this->renderNotFound(__('Page not found'));
        }
        $user = $request->user();
        $roleId = $user ? $user->role_id : config('shaun_core.role.id.guest');
        if (! $page->hasPermission($roleId) && (! in_array('web', $middlewares))) {
            throw new MessageHttpException(__("You don't have permission to view this page."));
        }

        return $this->renderData($request, $page->id);
    }
}
