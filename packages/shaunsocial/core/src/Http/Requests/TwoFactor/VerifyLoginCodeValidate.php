<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Exceptions\UserActiveHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\CodeVerify;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;
use Packages\ShaunSocial\Core\Traits\Utility;
use PragmaRX\Google2FA\Google2FA;

class VerifyLoginCodeValidate extends BaseFormRequest
{
    use Utility;

    public function rules()
    {
        return [
            'two_factor_code' => [
                'required',
                function ($attribute, $code, $fail) {
                    if ($code) {
                        $result = $this->checkCodeVerify(0, 'two_factory_code', $code, '', config('shaun_core.validation.two_factor_verify'));
                        if (! $result['status']) {
                            return $fail($result['message']);
                        }
                        $codeVerify = $result['codeVerify'];
                        $userTwoFactor = UserTwoFactor::getByUser($codeVerify->user_id, true);
                        if (! $userTwoFactor) {
                            return $fail(__('Do not support this method.'));
                        }
                        $viewer = $userTwoFactor->getUser();
                        if (! $viewer->is_active) {
                            throw new UserActiveHttpException(400);
                        }
                    }                    
                },
            ],
            'code' => [
                'required',
            ]
        ];
    }

     public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $twoFactorCode = $data['two_factor_code'];

                $userVerify = CodeVerify::findByField('code', $twoFactorCode);
                $userTwoFactor = UserTwoFactor::findByField('user_id', $userVerify->user_id);
                $provider = $userTwoFactor->getProvider();
                $params = $userTwoFactor->getParams();
                $viewer = $userTwoFactor->getUser();
                $code = $data['code'];
                
                switch ($provider->type) {
                    case 'sms':
                        $result = $this->checkCodeVerify($viewer->id, 'two_factor_setup_send_sms', $code, $params['phone_number']);
                        if (! $result['status']) {
                            return $validator->errors()->add('code', $result['message']);
                        }
                        break;
                    case 'mail':
                        $result = $this->checkCodeVerify($viewer->id, 'two_factor_setup_send_email', $code, $params['email']);
                        if (! $result['status']) {
                            return $validator->errors()->add('code', $result['message']);
                        }
                        break;
                    case 'auth_app':
                        $google2fa = new Google2FA();
                        if (!$google2fa->verifyKey($params['secret_key'], $code)) {
                            return $validator->errors()->add('code', __('The code is incorrect.'));
                        }
                        break;
                }
            });
        }

        return $validator;
    }


    public function messages()
    {
        return [
            'two_factor_code.required' => __('The two-factor code is required.'),
            'code.required' => __('The code is required.')
        ];
    }
}
