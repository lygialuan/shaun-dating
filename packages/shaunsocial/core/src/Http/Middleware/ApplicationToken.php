<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Exception;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Traits\Utility;

class ApplicationToken
{
    use Utility;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        if (alreadyInstalled()) {
            if ($request->cookie('access_token')) {   
                $token = 'Bearer '.$request->cookie('access_token');
                $request->headers->set('Authorization', $token);
            }
        }

        return $next($request);
    }
}
