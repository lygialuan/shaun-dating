<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\GetCodeAppValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\GetLoginCurrentValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\RemoveValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\SendLoginCodeValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\SendSetupEmailValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\SendSetupPhoneValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\VerifyCodeAppValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\VerifyLoginCodeValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\VerifySetupEmailValidate;
use Packages\ShaunSocial\Core\Http\Requests\TwoFactor\VerifySetupSmsValidate;
use Packages\ShaunSocial\Core\Models\CodeVerify;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;
use Packages\ShaunSocial\Core\Repositories\Api\TwoFactorRepository;
use Packages\ShaunSocial\Core\Traits\Utility;

class TwoFactorController extends ApiController
{
    use Utility;

    protected $twoFactorRepository;

    public function __construct(TwoFactorRepository $twoFactorRepository)
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $user = Auth::user();
                if (! setting('feature.enable_two_factor') || $user->isPage()) {
                    throw new MessageHttpException(__('Do not support this method.'));
                }
                
            }
            return $next($request);
        });
        
        $this->twoFactorRepository = $twoFactorRepository;
        parent::__construct();
    }

    public function getWhitelistForceLogin()
    {
        return [
            'get_login_current',
            'send_login_code',
            'verify_login_code'
        ];
    }

    public function get_current(Request $request)
    {
        $result = $this->twoFactorRepository->get_current($request->user());

        return $this->successResponse($result);
    }

    public function remove(RemoveValidate $request)
    {
        $this->twoFactorRepository->remove($request->user());

        return $this->successResponse();
    }

    public function send_setup_email(SendSetupEmailValidate $request)
    {
        $result =$this->twoFactorRepository->send_setup_email($request->email, $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function verify_setup_email(VerifySetupEmailValidate $request)
    {
        $result = $this->twoFactorRepository->verify_setup_email($request->email, $request->user());

        return $this->successResponse($result);
    }

    public function send_setup_phone(SendSetupPhoneValidate $request)
    {
        $result = $this->twoFactorRepository->send_setup_phone($request->phone_number, $request->user());
        
        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function verify_setup_phone(VerifySetupSmsValidate $request)
    {
        $result = $this->twoFactorRepository->verify_setup_sms($request->phone_number, $request->user());

        return $this->successResponse($result);
    }

    public function get_code_app(GetCodeAppValidate $request)
    {
        $result = $this->twoFactorRepository->get_code_app($request->user());

        return $this->successResponse($result);
    }

    public function verify_code_app(VerifyCodeAppValidate $request)
    {
        $result = $this->twoFactorRepository->verify_code_app($request->user());

        return $this->successResponse($result);
    }

    public function send_login_code(SendLoginCodeValidate $request)
    {
        $result = $this->twoFactorRepository->send_login_code($request->two_factor_code);

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function get_login_current(GetLoginCurrentValidate $request)
    {
        $result = $this->twoFactorRepository->get_login_current($request->two_factor_code);

        return $this->successResponse($result);
    }

    public function verify_login_code(VerifyLoginCodeValidate $request)
    {
        $userVerify = CodeVerify::findByField('code', $request->two_factor_code);
        $userTwoFactor = UserTwoFactor::findByField('user_id', $userVerify->user_id);

        $response = $this->loginUserBase($userTwoFactor->getUser(), $request);

        return $this->accessTokenMessageRespone($response, $response['token']);
    }
}
