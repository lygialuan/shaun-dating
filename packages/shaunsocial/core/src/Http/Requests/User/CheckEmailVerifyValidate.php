<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\Core\Traits\Utility;

class CheckEmailVerifyValidate extends UserValidate
{
    use Utility;
    
    public function rules()
    {
        $viewer = $this->user();

        return [
            'code' => [
                'required',
                'string',
                function ($attribute, $code, $fail) use ($viewer) {
                    if ($code) {
                        $result = $this->checkCodeVerify($viewer->id, 'email_verify', $code, $viewer->email);
                        if (! $result['status']) {
                            return $fail($result['message']);
                        }
                    }                    
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewer = $this->user();
                if ($viewer->email_verified) {
                    return $validator->errors()->add('user', __('The user already verify email.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'code.required' => __('The code is required.')
        ];
    }
}
