<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class forceInstall
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        $route = Route::current();

        if (! alreadyInstalled() && $route->getPrefix() != 'install') {
            return redirect()->route('install.requirements');
        }

        return $next($request);
    }
}
