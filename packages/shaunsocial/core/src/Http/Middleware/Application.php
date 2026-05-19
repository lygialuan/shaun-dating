<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Carbon\FactoryImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\Theme;
use Packages\ShaunSocial\Core\Models\SettingGroup;

class Application
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        if (env('ADMIN_FORCE') && ! request()->is(env('APP_ADMIN_PREFIX', 'admin').'/*')) {
            return redirect()->route('admin.dashboard.index');
        }

        if (request()->is(env('APP_ADMIN_PREFIX', 'admin').'/*')) {
            $user = Auth::guard('admin')->user();
            if ($user) {
                Auth::setUser(
                    Auth::guard('admin')->user()
                );
            }
        }
        
        if (alreadyInstalled()) {
            $languages = Language::getAll(false);
            $defaultLanguage = $languages->first(function ($value, $key) {
                return $value->is_default;
            });

            App::setLocale($defaultLanguage->key);
            $keyLanguages = $languages->pluck('name', 'key')->all();

            $theme = Theme::getActive();

            if ($request->is(env('APP_ADMIN_PREFIX', 'admin').'/*')){
                $generalSettings = SettingGroup::findByField('key', 'general');
                view()->share('generalSettings', $generalSettings);
            }
            

            view()->share('languages', $keyLanguages);
            view()->share('rtl', $defaultLanguage->is_rtl);
            view()->share('theme', $theme);

            FactoryImmutable::getDefaultInstance()->setHumanDiffOptions(Carbon::JUST_NOW);
            Carbon::setLocale(App::getLocale());
        }

        return $next($request);
    }
}
