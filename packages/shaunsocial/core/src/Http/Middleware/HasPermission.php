<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Exceptions\PermissionHttpException;
use Packages\ShaunSocial\Core\Models\Permission;
use Stripe\Exception\PermissionException;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();
        if (! $user) {
            $user = getUserGuest();
        }
        $permissions = explode('|', $permission);
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }
        $message = Permission::getMessageErrorByKey($permissions[0]);
        if ($request->is(env('APP_ADMIN_PREFIX', 'admin').'/*')) {
            return abort(config('shaun_core.core.permission_code'), $message);
        } else {
            throw new PermissionHttpException($message);
        }
        
    }
}
