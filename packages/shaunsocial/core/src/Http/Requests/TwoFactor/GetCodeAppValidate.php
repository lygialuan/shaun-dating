<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Http\Requests\TwoFactValidate;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class GetCodeAppValidate extends TwoFactValidate
{
    public function authorize()
    {
        if (parent::authorize()) {
            $provider = TwoFactorProvider::getByType('auth_app');
            return $provider;
        }
        
        return false;
    }
    
    public function rules()
    {
        return [
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ]
        ];
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.')
        ];
    }
}
