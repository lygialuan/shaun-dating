<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\Core\Models\InternalApi;
use Packages\ShaunSocial\Core\Traits\ApiResponser;

class ApplicationInternalApi
{
    use ApiResponser;
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
            $key = $request->header('key');            
            if (! $key) {
                return $this->errorMessageRespone(__('Api key not found.'));
            }

            $api = InternalApi::findByField('key', $key);
            if (! $api) {
                return $this->errorMessageRespone(__('Api key was incorrect.'));
            }

            Log::channel('shaun_internal')->info($request->route()->getName());
            Log::channel('shaun_internal')->info($key);
            Log::channel('shaun_internal')->debug(print_r($request->all(),true));
        }

        return $next($request);
    }
}
