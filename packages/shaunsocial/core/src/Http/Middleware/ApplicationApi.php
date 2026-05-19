<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Carbon\Carbon;
use Carbon\FactoryImmutable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Traits\ApiResponser;

class ApplicationApi
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
            $languages = Language::getAll(false);
            $defaultLanguage = $languages->first(function ($value, $key) {
                return $value->is_default;
            });

            App::setLocale($defaultLanguage->key);
            $keyLanguages = $languages->pluck('name', 'key')->all();

            $language = $request->hasHeader('Accept-Language') ? $request->header('Accept-Language') : '';
            if ($language && array_key_exists($language, $keyLanguages)) {
                App::setLocale($language);
            }

            $route = Route::current();
            $routeNameList = ['app_init', 'utility_check_access_code', 'user_me'];

            if (setting('site.offline') && !in_array($route->getName(), $routeNameList)) {
                $accessCode = $request->header('Access-Code');
                if ($accessCode != setting('site.offline_code')) {
                    return $this->messageWithCodeResponse('', 'offline', 503);
                }
            }

            if ($request->bearerToken()) {
                $user = Auth::guard('sanctum')->user();
                if ($user) {
                    Auth::setUser(
                        Auth::guard('sanctum')->user()
                    );
                }
            }

            $user = Auth::user();            
            if ($user) {
                if ($user->isPage() && ! $user->getParent()) {
                    throw new AuthenticationException();
                }
                if ($user->language && array_key_exists($user->language, $keyLanguages)) {
                    App::setLocale($user->language);
                }

                //Check active user
                $routeNameList = ['auth_logout'];
                if (! $user->is_active && ! in_array($route->getName(), $routeNameList)) {
                    return $this->messageWithCodeResponse(__('Your account is pending approval.'), 'inactive', 403);
                }

                //Check email validate
                $routeNameList = ['user_me', 'user_ping', 'auth_logout', 'app_init', 'user_store_language'];
                
                $routeNameListEmail = array_merge($routeNameList, ['user_send_email_verify', 'user_check_email_verify']);
                if (setting('feature.email_verify') && ! $user->isPage() && ! $user->isModerator() && ! $user->email_verified && ! in_array($route->getName(), $routeNameListEmail)) {
                    return $this->messageWithCodeResponse(__('Your account has not been confirmed by email.'), 'email_validate', 403);
                }

                //check phone validate
                $routeNameListPhone = array_merge($routeNameListEmail,['user_send_phone_verify', 'user_check_phone_verify', 'user_change_phone_verify', 'user_change_phone_when_edit']);
                if (setting('feature.phone_verify') && ! $user->isPage() && ! $user->isModerator() && ! $user->phone_verified && ! in_array($route->getName(), $routeNameListPhone)) {
                    return $this->messageWithCodeResponse(__('Your account has not been confirmed by phone number.'), 'phone_validate', 403);
                }

                $routeNameListPhotos = array_merge($routeNameListPhone,['user_upload_photos_verify', 'user_remove_photo_verify', 'user_change_main_photo', 'user_completed_photo_verify']);
                if (setting('feature.photos_verify') && ! $user->isPage() && ! $user->isModerator() && ! $user->photos_verified && ! in_array($route->getName(), $routeNameListPhotos)) {
                    return $this->messageWithCodeResponse(__('Your account has not been confirmed by photos.'), 'photos_validate', 403);
                }

                $routeNameListIdentity = array_merge($routeNameListPhotos,['user_verify_store_request', 'user_verify_upload_file', 'user_verify_get_files', 'user_verify_delete_file']);
                if (setting('feature.identity_verify') && ! $user->isPage() && ! $user->isModerator() && ! $user->identity_verified && ! in_array($route->getName(), $routeNameListIdentity)) {
                    return $this->messageWithCodeResponse(__('Your account has not been confirmed identity.'), 'identity_validate', 403);
                }
            }

            FactoryImmutable::getDefaultInstance()->setHumanDiffOptions(Carbon::JUST_NOW);
            Carbon::setLocale(App::getLocale());
        }

        return $next($request);
    }
}
