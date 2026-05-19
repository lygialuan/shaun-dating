<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Http\Requests\TwoFactValidate;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class VerifySetupEmailValidate extends TwoFactValidate
{
    use Utility;

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

                $result = $this->checkCodeVerify($viewer->id, 'two_factor_setup_send_email', $data['code'], $data['email']);
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
            'email.required' => __('The email is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'password.required' => __('The password is required.'),
            'code.required' => __('The code is required.'),
        ];
    }
}
