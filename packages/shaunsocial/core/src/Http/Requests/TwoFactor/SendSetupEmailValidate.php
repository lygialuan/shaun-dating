<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Http\Requests\TwoFactValidate;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class SendSetupEmailValidate extends TwoFactValidate
{
    public function authorize()
    {
        if (parent::authorize()) {
            $provider = TwoFactorProvider::getByType('mail');
            return $provider;
        }
        
        return false;
    }
    
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ]
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('The email is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'password.required' => __('The password is required.')
        ];
    }
}
