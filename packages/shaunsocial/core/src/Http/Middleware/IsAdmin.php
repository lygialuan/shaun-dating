<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Packages\ShaunSocial\Core\Models\Language;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->isModerator()) {
            if ($user->is_active) {
                $languages = Language::getAll(false);
                $keyLanguages = $languages->pluck('name', 'key')->all();
                $defaultLanguage = $languages->first(function ($value, $key) {
                    return $value->is_default;
                });
                App::setLocale($defaultLanguage->key);
                $user = $request->user();
                if ($user && array_key_exists($user->language, $keyLanguages)) {
                    App::setLocale($user->language);
                    $language = Language::getBykey($user->language);                    
                    view()->share('languageCurrent', $user->language);
                    view()->share('rtl', $language->is_rtl);
                } else {
                    view()->share('languageCurrent', $defaultLanguage->key);
                    view()->share('rtl', $defaultLanguage->is_rtl);
                }
                view()->share('languagesGlobal', $keyLanguages);

                return $next($request);
            } else {
                Session::flush();
                Auth::logout();
            }
        }
        $redirect = url()->current();
        return redirect()->route('admin.auth.index',['redirect' => base64_encode($redirect)]);
    }
}
