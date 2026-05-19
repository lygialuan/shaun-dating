<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\User\ChangePasswordValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\CheckCodeForgotPasswordValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreBlockValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\GetUserProfileValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\SearchUserValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreDarkmodeValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreEditProfileValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreEmailSettingValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreNotificationSettingValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StorePrivacySettingValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\UploadImageValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\CheckEmailVerifyValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\CheckPasswordValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\DeleteUserValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\SendCodeForgotPasswordValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\SendEmailVerifyValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreAccountValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreDownloadValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreForgotPasswordValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreLanguageValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreVideoAutoPlayValidate;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Http\Requests\User\ChangePhoneVerifyValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\ChangePhoneWhenEditValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\CheckPhoneVerifyValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\CheckPhoneWhenEditValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\GetBlockValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\GetEmailSettingValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\SendAddEmailPasswordVerifyValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\SendPhoneVerifyValidate;
use Packages\ShaunSocial\Core\Http\Requests\User\StoreAddEmailPasswordVerifyValidate;
use Packages\ShaunSocial\Core\Http\Requests\UserValidate;

class UserController extends ApiController
{
    protected $userRepository;

    public function getWhitelistForceLogin()
    {
        return [
            'me',
            'check_email_verify',
            'send_code_forgot_password',
            'check_code_forgot_password',
            'store_forgot_password'
        ];
    }

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    public function me(Request $request)
    {
        $result = $this->userRepository->me($request->user());

        return $this->successResponse($result);
    }

    public function search(Request $request)
    {
        $result = $this->userRepository->search($request->text, 
            $request->only([
                'only_user',
                'not_me',
                'not_parent'
            ]),
            $request->user()
        );

        return $this->successResponse($result);
    }

    public function suggest_signup(Request $request)
    {
        $result = $this->userRepository->suggest_signup($request->user(),$request->text);

        return $this->successResponse($result);
    }

    public function trending(Request $request)
    {
        $result = $this->userRepository->trending($request->user());

        return $this->successResponse($result);
    }

    public function suggest(Request $request)
    {
        $result = $this->userRepository->suggest($request->user());

        return $this->successResponse($result);
    }

    public function profile(GetUserProfileValidate $request)
    {
        $result = $this->userRepository->profile($request->user_name, $request->user());

        return $this->successResponse($result);
    }

    public function store_block(StoreBlockValidate $request)
    {
        $this->userRepository->store_block($request->all(), $request->user());

        return $this->successResponse();
    }

    public function block(GetBlockValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $type = $request->type ? $request->type : 'all';

        $result = $this->userRepository->block($request->user(), $type, $page);

        return $this->successResponse($result);
    }

    public function notification_setting(Request $request)
    {
        $result = $this->userRepository->notification_setting($request->user());

        return $this->successResponse($result);
    }

    public function store_notification_setting(StoreNotificationSettingValidate $request)
    {        
        $this->userRepository->store_notification_setting($request->all(), $request->user());
    
        return $this->successResponse();
    }

    public function privacy_setting(Request $request)
    {
        $result = $this->userRepository->privacy_setting($request->user());

        return $this->successResponse($result);
    }

    public function store_privacy_setting(StorePrivacySettingValidate $request)
    {        
        $this->userRepository->store_privacy_setting($request->all(), $request->user());
    
        return $this->successResponse();
    }

    public function email_setting(GetEmailSettingValidate $request)
    {
        $result = $this->userRepository->email_setting($request->user());

        return $this->successResponse($result);
    }

    public function store_email_setting(StoreEmailSettingValidate $request)
    {
        $this->userRepository->store_email_setting($request->all(), $request->user());

        return $this->successResponse();
    }

    public function ping(Request $request)
    {
        $result = $this->userRepository->ping($request->user());

        return $this->successResponse($result);
    }

    public function store_login_first(Request $request)
    {
        $this->userRepository->store_login_first($request->user());

        return $this->successResponse();
    }
    
    public function store_darkmode(StoreDarkmodeValidate $request)
    {
        $this->userRepository->store_darkmode($request->user(), $request->darkmode);
    
        return $this->successResponse();
    }

    public function store_video_auto_play(StoreVideoAutoPlayValidate $request)
    {
        $this->userRepository->store_video_auto_play($request->user(), $request->enable);
    
        return $this->successResponse();
    }

    public function suggest_search(SearchUserValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->userRepository->suggest_search($request->query('query'), $request->user(), $page);
    
        return $this->successResponse($result);
    }

    public function trending_search(SearchUserValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->userRepository->trending_search($request->query('query'), $request->user(), $page);
    
        return $this->successResponse($result);
    }

    public function upload_cover(UploadImageValidate $request)
    {
        $result = $this->userRepository->upload_cover($request->file('file'), $request->user());
    
        return $this->successResponse($result);
    }

    public function upload_avatar(UploadImageValidate $request)
    {
        $result = $this->userRepository->upload_avatar($request->file('file'), $request->user());

        return $this->successResponse($result);
    }

    public function get_edit_profile(UserValidate $request)
    {
        $result = $this->userRepository->get_edit_profile($request->user());

        return $this->successResponse($result);
    }

    public function store_edit_profile(StoreEditProfileValidate $request)
    {
        $data = $request->safe()->all();
        if (!empty($data['links'])) {
            $links = [];
            if (is_array($data['links'])) {
                foreach ($data['links'] as $link) {
                    $links[] = [
                        'title' => ! empty($link['title']) ? $link['title'] : '',
                        'link' => $link['link']
                    ];
                }
                
                $data['links'] = json_encode($data['links']);
            }
        }

        if (empty($data['country_id'])) {
            $data['country_id'] = 0;
        }

        if (!empty($data['address'])) {
            $data['location'] = $data['address'];
        }

        if (array_key_exists('gender_id', $data) && $data['gender_id'] === '') {
            $data['gender_id'] = 0;
        }

        $result = $this->userRepository->store_edit_profile($data,$request->user());
    
        return $this->successResponse($result);
    }

    public function send_email_verify(SendEmailVerifyValidate $request)
    {
        $this->userRepository->send_email_verify($request->user());

        return $this->successResponse();
    }

    public function check_email_verify(CheckEmailVerifyValidate $request)
    {
        $this->userRepository->check_email_verify($request->code, $request->user());
    
        return $this->successResponse();
    }

    public function change_password(ChangePasswordValidate $request)
    {
        $this->userRepository->change_password($request->password_new, $request->user());
    
        return $this->successResponse();
    }

    public function send_code_forgot_password(SendCodeForgotPasswordValidate $request)
    {
        $this->userRepository->send_code_forgot_password($request->email);
        
        return $this->successResponse();
    }

    public function check_code_forgot_password(CheckCodeForgotPasswordValidate $request)
    {
        return $this->successResponse();
    }

    public function store_forgot_password(StoreForgotPasswordValidate $request)
    {
        $this->userRepository->store_forgot_password($request->email, $request->password);
    
        return $this->successResponse();
    }

    public function check_password(CheckPasswordValidate $request)
    {
        return $this->successResponse();
    }

    public function store_account(StoreAccountValidate $request)
    {
        $this->userRepository->store_account($request->only(['email','user_name']), $request->user());
    
        return $this->successResponse();
    }

    public function delete(DeleteUserValidate $request){
        
        $this->userRepository->delete($request->user());

        $response = $this->successResponse();
        if ($request->headers->get('SupportCookie')) {
            $response = setAppCookie($response, 'access_token', null, -1);
        }

        return $response;
    }

    public function store_language(StoreLanguageValidate $request)
    {
        $this->userRepository->store_language($request->key, $request->user());
        
        return $this->successResponse();
    }

    public function get_download(Request $request)
    {
        $result = $this->userRepository->get_download($request->user());

        return $this->successResponse($result);
    }

    public function store_download(StoreDownloadValidate $request)
    {
        $this->userRepository->store_download($request->user());

        return $this->successResponse();
    }

    public function send_add_email_password_verify(SendAddEmailPasswordVerifyValidate $request)
    {
        $this->userRepository->send_add_email_password_verify($request->user(), $request->all());

        return $this->successResponse();
    }

    public function store_add_email_password_verify(StoreAddEmailPasswordVerifyValidate $request)
    {
        $this->userRepository->store_add_email_password_verify($request->user(), $request->all());

        return $this->successResponse();
    }

    public function remove_login_other_device(CheckPasswordValidate $request)
    {
        $this->userRepository->remove_login_other_device($request->user(), $request->post('fcm_token'));

        return $this->successResponse();
    }

    public function send_phone_verify(SendPhoneVerifyValidate $request)
    {
        $result = $this->userRepository->send_phone_verify($request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function check_phone_verify(CheckPhoneVerifyValidate $request)
    {
        $this->userRepository->check_phone_verify($request->user());

        return $this->successResponse();
    }

    public function change_phone_verify(ChangePhoneVerifyValidate $request)
    {
        $result = $this->userRepository->change_phone_verify($request->phone_number, $request->user(), true);

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function change_phone_when_edit(ChangePhoneWhenEditValidate $request)
    {
        $result = $this->userRepository->change_phone_verify($request->phone_number, $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function check_phone_when_edit(CheckPhoneWhenEditValidate $request)
    {
        $this->userRepository->check_phone_when_edit($request->phone_number, $request->user());

        return $this->successResponse();
    }

    public function upload_photos_verify(UploadImageValidate $request)
    {
        $result = $this->userRepository->upload_photos_verify($request->file('file'), $request->user(), $request->position, $request->isMain);

        return $this->successResponse($result);
    }
    
    public function remove_photo_verify(Request $request)
    {
        $result = $this->userRepository->remove_photo_verify($request->user(), $request->id);

        return $this->successResponse($result);
    }
       
    public function change_main_photo(Request $request)
    {
        $result = $this->userRepository->change_main_photo($request->user(), $request->photoId, $request->isMain);

        return $this->successResponse($result);
    }

    public function completed_photo_verify(Request $request)
    {
        $result = $this->userRepository->completed_photo_verify($request->user());

        return $this->successResponse($result);
    }
   
    public function get_all_users(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $filters = $request->except('page');
        $result = $this->userRepository->get_all_users( $request->user(), $page, $filters);
        return $this->successResponse($result);
    }
}
