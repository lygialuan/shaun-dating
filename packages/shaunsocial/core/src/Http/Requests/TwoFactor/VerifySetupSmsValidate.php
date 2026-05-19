<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Http\Requests\TwoFactValidate;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\Core\Validation\PhoneValidation;

class VerifySetupSmsValidate extends TwoFactValidate
{
    use Utility;

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
            ],
            'code' => 'required'
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewer = $this->user();

                $data = $validator->getData();

                $result = $this->checkCodeVerify($viewer->id, 'two_factor_setup_send_sms', $data['code'], $data['phone_number']);
                if (! $result['status']) {
                    return $validator->errors()->add('code', $result['message']);
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'phone_number.required' => __('The phone number is required.'),
            'password.required' => __('The password is required.'),
            'code.required' => __('The code is required.'),
        ];
    }
}
