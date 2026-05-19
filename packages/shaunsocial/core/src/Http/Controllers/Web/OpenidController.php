<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Web;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\OpenidProvider;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Library\OpenIDConnectClient;
use Packages\ShaunSocial\Core\Models\OpenidProviderUser;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Traits\ApiResponser;
use Packages\ShaunSocial\Core\Traits\Utility;


class OpenidController extends Controller
{
    use Utility, ApiResponser;
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function auth(Request $request)
    {
        $provider = OpenidProvider::findByField('app_name', $request->name);
        if (! $provider || ! $provider->is_active) {
            abort(404, __('Provider not found.'));
        }

        $isApp = $request->app;

        $scheme = $request->query('scheme');
        if ($scheme) {
            $request->session()->put('scheme', $scheme);
            $request->session()->save();
        }

        $openIdClient = new OpenIDConnectClient(
            $provider->server,
            $provider->client_id,
            $provider->client_secret
        );
		
        $providerConfigParam = [
            'authorization_endpoint'=> $provider->authorize_endpoint,
            'token_endpoint'=> $provider->access_token_endpoint,
            'userinfo_endpoint'=> $provider->get_user_info_endpoint,
        ];

        // custom Linkedin
        if($request->name == 'Linkedin') {
            $providerConfigParam['token_endpoint_auth_methods_supported'] = array('none');
        }
		
		if($request->name == 'Facebook') {
			$openIdClient->needCheckAccessToken = false;
		}

        if($request->name == 'Twitter') {
            $openIdClient->scopeOpenid = false;
            $openIdClient->codeVerifierRandomCount = 32;
            $openIdClient->needCheckAccessToken = false;
            $providerConfigParam['token_endpoint_auth_methods_supported'] = array('client_secret_basic');

            $providerConfigParam['code_challenge_methods_supported'] = array('plain');
            $openIdClient->setCodeChallengeMethod('plain');
        }

        if($request->name == 'Apple') {
            $openIdClient->scopeOpenid = false;
            $openIdClient->addAuthParam(array('response_mode' => 'form_post'));
        }

        $openIdClient->providerConfigParam($providerConfigParam);

        if ($provider->scope) {
            $openIdClient->addScope($provider->scope);
        }        
        if ($isApp) {
            $openIdClient->setRedirectURL(route('web.openid.auth',['name' => $request->name, 'app' => 1]));
        } else {
            $openIdClient->setRedirectURL(route('web.openid.auth',['name' => $request->name]));
        }        

        try {
            $result = $openIdClient->authenticate();
            if ($result !== true) {
                return $result;
            }
            if($request->name == 'Apple') { 
                $appleInfo =  json_decode(array_key_exists('user', $_GET) ? $_GET['user'] : (array_key_exists('user', $_POST) ? $_POST['user'] : '[]'), true) ?: array();
                $appleName = '';
                if (isset($appleInfo['name'])) {
                    $appleName = $appleInfo['name']['firstName'] .' ' . $appleInfo['name']['lastName'];
                }
                if (! $appleName) {
                    $emailTmp = explode('@', $openIdClient->getVerifiedClaims()->email);
                    $appleName = Str::slug($emailTmp[0], '_');
                }
                $userInfo = [
                    'sub' => $openIdClient->getVerifiedClaims()->sub,
                    'email' => $openIdClient->getVerifiedClaims()->email,
                    'name' => $appleName
                ];

            } else {
                $userInfo = $openIdClient->requestUserInfo();
                if (empty($userInfo)) {
                    throw new Exception(__('Error when getting user information.'));
                }
            }
            

            $userInfo = json_decode(json_encode($userInfo),true);

            if($request->name == 'Twitter') {
                $userInfo = @$userInfo['data'];
            }

            if (!isset($userInfo[$provider->user_id_map]) || !isset($userInfo[$provider->name_map])) {
                throw new Exception(__('An attribute mapping error has occurred.'));
            }

            $name = $userInfo[$provider->name_map];
            $email = '';
            if ($provider->email_map) {
                $email = ! empty($userInfo[$provider->email_map]) ? $userInfo[$provider->email_map] : '';
            }

            $identifier = $userInfo[$provider->user_id_map];
            $openIdUser = OpenidProviderUser::checkProviderIdentity($provider->id, $identifier);
            if (! $openIdUser) {
                if ($email) {
                    $emailTmp = explode('@', $email);
                    $userName = Str::slug($emailTmp[0], '_');
                } else {
                    $userName = Str::slug($name, '_');
                }                
                
                if (Str::length($userName) < config('shaun_core.core.user_name_min_length')) {
                    $userName.=str_repeat('0', config('shaun_core.core.user_name_min_length') - Str::length($userName));
                }

                $userNameTmp = $userName;                
                $number = 0;
                do {                
                    if ($number) {
                        $userNameTmp = $userName.$number;
                    }

                    $data = User::where('user_name', $userNameTmp)->get();
                    if (!$data->count()) {
                        break;
                    }
                    $number++;
                }
                while (true);
                
                $userName = $userNameTmp;                
                $hasEmail = true;
                if (! $email) {
                    $email = $userName.'@'.config('shaun_core.core.email_domain_default');                    
                    $hasEmail = false;
                }

                //check email exist
                $user = User::findByField('email', $email);
                if (! $user) {
                    if (! setting('feature.enable_signup')) {
                        abort(400, __('You cannot sign up this site.'));
                    }

                    if (checkEmailBan($email)) {
                        abort(400, __('The email has been banned.'));
                    }

                    if (checkUsernameBan($userName)) {
                        abort(400, __('The username has been banned.'));
                    }

                    $password = Str::random(10);
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'user_name' => $userName,
                        'email_verified' => true,
                        'password' => Hash::make($password),
                        'has_email' => $hasEmail
                    ]);

                    if ($hasEmail) {
                        //send email 
                        $this->userRepository->send_email_after_register($user, $password);
                    }
                    if ($provider->avatar_map) {
                        $photo = checkRecursiveKeyArray($provider->avatar_map, $userInfo);
                        if ($photo && is_string($photo)) {
                            $photo = str_replace('_normal.', '.', $photo);
                            $photoPath = File::downloadPhoto($photo);                            
    
                            if ($photoPath) {
                                $storageFile = File::storePhoto($photoPath, [
                                    'parent_type' => 'user_avatar',
                                    'user_id' => $user->id,
                                    'parent_id' => $user->id,
                                    'resize_size' => [
                                        'real' => true,
                                        'width' => config('shaun_core.file.avatar.width'),
                                        'height' => config('shaun_core.file.avatar.height'),
                                    ]
                                ]);
    
                                if ($storageFile) {
                                    $user->update([
                                        'avatar_file_id' => $storageFile->id
                                    ]);
                                }                  
                            }
                        }
                    }
                }
                OpenidProviderUser::create([
                    'provider_id' => $provider->id,
                    'user_id' => $user->id,
                    'provider_uid' => $identifier,
                    'access_token' => $openIdClient->getAccessToken()
                ]);

            } else {                
                $user = User::findByField('id', $openIdUser->user_id);
            }
            $token = $user->createToken('authToken')->plainTextToken;

            if ($request->session()->get('scheme') || $isApp) {
                $code = $this->createCodeVerify($user->id, 'login', '', 8);
                return Redirect::to('shaunapp://access_code='.$code);
            } else {
                $response = response(view('shaun_core::app', ['mustLogin' => true]));
                return setAppCookie($response, 'access_token', $token, config('shaun_core.core.time_coookie_login'));
            }
        } catch (Exception $ex) {
            $code = $request->get('error');
            $codeRedirect = [
                'user_cancelled_login',
                'access_denied'
            ];

            if (in_array($code, $codeRedirect)) {
                if ($request->session()->get('scheme') || $isApp) {
                    return Redirect::to('shaunapp://access_code=');
                } else {
                    return Redirect::to(setting('site.url'));
                }
            }
            abort(400, $ex->getMessage());
        }

    }

    public function delete_user(Request $request)
    {
        return $this->successResponse();
    }
}
