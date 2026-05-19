<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Closure;

class canInstall
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
        if ($this->alreadyInstalled()) {
            $installedRedirect = config('shaun_core_install.installedAlreadyAction');

            switch ($installedRedirect) {

                case 'route':
                    $routeName = config('shaun_core_install.installed.redirectOptions.route.name');
                    $data = config('shaun_core_install.installed.redirectOptions.route.message');

                    return redirect()->route($routeName)->with(['data' => $data]);
                    break;

                case 'abort':
                    abort(config('shaun_core_install.installed.redirectOptions.abort.type'));
                    break;

                case 'dump':
                    $dump = config('shaun_core_install.installed.redirectOptions.dump.data');
                    dd($dump);
                    break;

                case '404':
                case 'default':
                default:
                    abort(404);
                    break;
            }
        }

        return $next($request);
    }

    /**
     * If application is already installed.
     *
     * @return bool
     */
    public function alreadyInstalled()
    {
        $installedLogFile = storage_path('installed');

        if (! file_exists(base_path('.env')) && ! file_exists(storage_path('installed'))) {
            $dateStamp = date('Y/m/d h:i:sa');
            file_put_contents($installedLogFile, $dateStamp);
        }

        return ! file_exists($installedLogFile);
    }
}
