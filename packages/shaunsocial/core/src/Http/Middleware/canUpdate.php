<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Closure;

class canUpdate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if the application has not been installed,
        // redirect to the installer
        if (! alreadyInstalled()) {
            return redirect()->route('install.requirements');
        }

        if ($this->alreadyUpdated()) {
            return redirect()->route('web.home.index');
        }

        return $next($request);
    }

    /**
     * If application is already updated.
     *
     * @return bool
     */
    public function alreadyUpdated()
    {
        $version = setting('site.version');
        //TODO get current version from DB
        $currentVersion = file_get_contents(public_path('version.txt'));

        if ($version == $currentVersion) {
            return true;
        }

        // Continue, the app needs an update
        return false;
    }
}
