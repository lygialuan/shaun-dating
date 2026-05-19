<?php


namespace Packages\ShaunSocial\Core\Exceptions;

use App\Exceptions\Handler as BaseHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Packages\ShaunSocial\Core\Traits\ApiResponser;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Support\Facades\App;
use Packages\ShaunSocial\Core\Models\Language;
use Illuminate\Support\Facades\Auth;

class Handler extends BaseHandler
{
    use ApiResponser;

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            //cheat set language
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
            
            $user = Auth::user();            
            if ($user) {
                if ($user->language && array_key_exists($user->language, $keyLanguages)) {
                    App::setLocale($user->language);
                }
            }

            if ($e instanceof NotFoundHttpException || $e instanceof MethodNotAllowedHttpException) {
                $message = $e->getMessage() ? $e->getMessage() : __('Api not found');                
                return $this->errorNotFound($message);
            }

            if ($e instanceof AuthenticationException) {
                if ($user) {
                    $token = $user->currentAccessToken();
                    if ($token) {
                        $token->delete();
                    }
                }

                $response = $this->messageWithCodeResponse(__('Not authenticated.'), 'authenticated', 401);
                if ($request->headers->get('SupportCookie')) {
                    $response = setAppCookie($response, 'access_token', null, -1);
                }
                return $response;
            }

            if ($e instanceof ThrottleRequestsException) {
                switch ($request->route()->getName()) {
                    case 'auth_login':
                        return $this->messageWithCodeResponse(__('Too many failed login attempts.'), 'too_many_requests', 429);
                        break;
                };

                return $this->messageWithCodeResponse(__('Too many requests in 1 minute. Try again later.'), 'too_many_requests', 429);
            }

            if ($e instanceof MessageHttpException || $e instanceof AuthorizationException) {
                return $this->errorMessageRespone($e->getMessage());
            }

            if ($e instanceof PostTooLargeException) {
                return $this->messageWithCodeResponse(__('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFilesServer().'Kb']).'.', 'server_error', 413);
            }

            if ($e instanceof UserActiveHttpException) {
                return $this->messageWithCodeResponse(__('Your account is pending approval.'), 'inactive', 400);
            }

            if ($e instanceof HttpException && $e->getStatusCode() == 400) {
                return $this->messageWithCodeResponse(__('You do not have permission to do it.'), 'permission', 400);
            }

            if ($e instanceof PermissionHttpException || $e instanceof AuthorizationException) {
                return $this->messageWithCodeResponse($e->getMessage(), 'membership_permission', $e->getStatusCode());
            }

            //cheat for webp file
            if ($e instanceof FatalError && $e->getMessage() == 'gd-webp cannot allocate temporary buffer') {                
                return $this->messageWithCodeResponse(__("The file uploaded is in a format that we don't support."), 'error_message', 400);
            }

            $message = __('We are currently experiencing some technical issues. Please try again later.');
            if (config('app.debug')) {
                $message = $e->getMessage();
            }

            return $this->messageWithCodeResponse($message, 'server_error', 500);
        }
        if ($request->is(env('APP_ADMIN_PREFIX', 'admin').'/*')) {
            if ($e instanceof MessageHttpException) { 
                return redirect()->back()->withInput()->withErrors([
                    'message_error' => [
                        $e->getMessage()
                    ],
                ]);
            }
            if ($e instanceof PostTooLargeException) {
                session()->flash('admin_message_error', __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFilesServer().'Kb']).'.'); 
            }
        }
        if (! config('app.debug') && $e instanceof \Illuminate\Database\QueryException) {
            throw new HttpException(500 ,__('Error Establishing a Database Connection'));             
        }
        return parent::render($request, $e);
    }

    protected function getHttpExceptionView(HttpExceptionInterface $e)
    {
        if (! config('app.debug')) {
            return 'shaun_core::error';
        }
        
        parent::getHttpExceptionView($e);
    }
}
