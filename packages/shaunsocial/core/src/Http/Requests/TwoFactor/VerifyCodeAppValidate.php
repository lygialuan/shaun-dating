<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Http\Requests\TwoFactValidate;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use PragmaRX\Google2FA\Google2FA;

class VerifyCodeAppValidate extends TwoFactValidate
{   
    public function authorize()
    {
        if (parent::authorize()) {
            $provider = TwoFactorProvider::getByType('auth_app');
            if ($provider) {
                $user = $this->user();
                $userTwoFactor = UserTwoFactor::findByField('user_id', $user->id);
                return $userTwoFactor && $userTwoFactor->provider_id == $provider->id;
            }
        }
        
        return false;
    }

    public function rules()
    {
        return [
            'code' => [
                'required'
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ]
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                $data = $validator->getData();
                $userTwoFactor = UserTwoFactor::findByField('user_id', $user->id);
                $params = $userTwoFactor->getParams();
                $google2fa = new Google2FA();
                if (!$google2fa->verifyKey($params['secret_key'], $data['code'])) {
                    return $validator->errors()->add('code', __('The code is incorrect.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.'),
            'code.required' => __('The code is required.')
        ];
    }
}
