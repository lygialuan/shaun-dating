<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Packages\ShaunSocial\Core\Exceptions\UserActiveHttpException;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Auth\LoginValidate;
use Packages\ShaunSocial\Core\Http\Requests\Auth\LoginWithCodeValidate;
use Packages\ShaunSocial\Core\Http\Requests\Auth\SignupValidate;
use Packages\ShaunSocial\Core\Http\Resources\Utility\GenderResource;
use Packages\ShaunSocial\Core\Models\CodeVerify;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Core\Repositories\Api\InviteRepository;
use Packages\ShaunSocial\Core\Traits\Utility;

class AuthController extends ApiController
{
    use Utility;
    
    protected $userRepository;
    protected $inviteRepository;
    
    public function __construct(UserRepository $userRepository, InviteRepository $inviteRepository)
    {
        $this->userRepository = $userRepository;
        $this->inviteRepository = $inviteRepository;
    }
    
    protected function loginUser($user, $request)
    {
        if (! $user->is_active) {
            throw new UserActiveHttpException(400);
        }

        return $this->loginUserBase($user, $request);
    }

    public function login(LoginValidate $request)
    {
        $data = $request->only(['email', 'password']);
        $email = $data['email'];
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (setting('feature.phone_verify') && validatePhoneNumber($email)) {
                $user = User::checkExistPhoneNumber($email);
                $email = $user->email;
            } else {
                $user = User::findByField('user_name', $email);
                $email = $user->email;
            }
        } 
        $data['email'] = $email;
        if (! Auth::validate($data)) {
            return $this->errorMessageRespone(__('Your email or password was incorrect.'));
        }

        $user = User::findByField('email', $data['email']);
        if (! $user->is_active) {
            throw new UserActiveHttpException(400);
        }

        if (setting('feature.enable_two_factor') && UserTwoFactor::getByUser($user->id, true)) {
            $twoFactoryCode = $this->createCodeVerify($user->id, 'two_factory_code');
            return $this->errorResponse(__('Need verify two-factory'),['two_factory_code' => $twoFactoryCode], 'two_factor');
        }

        $response = $this->loginUser($user, $request);

        return $this->accessTokenMessageRespone($response, $response['token']);
    }

    public function login_with_code(LoginWithCodeValidate $request)
    {
        $codeVerify = CodeVerify::where('type', 'login')->where('code', $request->code)->first();
        $user = User::findByField('id', $codeVerify->user_id);
        $codeVerify->delete();
        
        $response = $this->loginUser($user, $request);

        return $this->accessTokenMessageRespone($response, $response['token']);
    }

    public function signup(SignupValidate $request)
    {
        $request->mergeIfMissing([
            'address' => '',
            'phone_number' => ''
        ]);
        $data = $request->all();
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'user_name' => $data['user_name'],
            'password' => Hash::make($data['password']),
        ];

        if (setting('feature.require_birth')) {
            $userData['birthday'] = $data['birthday'];
        }

        if (setting('feature.require_gender')) {
            $userData['gender_id'] = $data['gender_id'];
        }

        if (setting('feature.require_location')) {
            $countryData = getCountryData();
            $request->mergeIfMissing($countryData);

            $userData = array_merge($userData, $request->all(array_keys($countryData)));
            $userData = cleanCountryData($userData);
            $userData['location'] = $userData['address'];
        }

        if (setting('feature.phone_verify')) {
            $userData['phone_number'] = $data['phone_number'];
        }
        
        $user = $this->userRepository->store($userData);

        if ($request->ref_code) {
            $this->inviteRepository->check($request->ref_code, $user);
        }

        $response = $this->loginUser($user, $request);

        return $this->accessTokenMessageRespone($response, $response['token']);
    }
    
    public function config(Request $request)
    {
        $result = [
            'signupField' => [
                'birthShow' => setting('feature.require_birth') ? true : false,
                'locationShow' => setting('feature.require_location') ? true : false,
                'genderShow' => setting('feature.require_gender') ? true : false,
            ],
            'genders' => GenderResource::collection(Gender::getAll()),
            'inviteOnly' => setting('feature.invite_only') ? true : false
        ];
        
        return $this->successResponse($result);
    }
    
    public function logout(Request $request)
    {     
        if ($request->has('fcm_token')){
            $viewer = $request->user();
            $viewer->deleteFcmToken($request->fcm_token);
        }

        $response = $this->successResponse();
        if ($request->headers->get('SupportCookie')) {
            $response = setAppCookie($response, 'access_token', null, -1);
        }

        return $response;
    }
}
