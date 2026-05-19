<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Http\Requests\TwoFactValidate;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\Core\Validation\PhoneValidation;

class SendSetupPhoneValidate extends TwoFactValidate
{
    public function authorize()
    {
        if (parent::authorize()) {
            $provider = TwoFactorProvider::getByType('sms');
            return $provider;
        }
        
        return false;
    }
    
    public function rules()
    {
        return [
            'phone_number' => [
                'required', 
                new PhoneValidation()
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ]
        ];
    }

    public function messages()
    {
        return [
            'phone_number.required' => __('The phone number is required.'),
            'password.required' => __('The password is required.')
        ];
    }
}
