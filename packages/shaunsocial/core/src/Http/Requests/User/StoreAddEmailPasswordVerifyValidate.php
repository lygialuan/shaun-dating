<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PasswordValidation;

class StoreAddEmailPasswordVerifyValidate extends UserValidate
{
    use Utility;
    
    public function rules()
    {
        return [
            'email' => [
                'required',
                'string',
                'max:255',
                'email',
                'unique:users',
                'email',
                function ($attribute, $email, $fail) {
                    if (checkEmailBan($email)) {
                        return $fail(__('The email has been banned.'));
                    }
                },
            ],
            'password' => ['required', 'string', new PasswordValidation()],
            'password_confirmed' => ['required'],
            'code' => ['required','string']
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewer = $this->user();
                if ($viewer->has_email) {
                    return $validator->errors()->add('user', __('The user already has email.'));
                }

                $data = $validator->getData();

                if (checkEmailBan($data['email'])) {
                    return $validator->errors()->add('email', __('The email has been banned.'));
                }

                if ($data['password'] != $data['password_confirmed']) {
                    return $validator->errors()->add('password_confirmed', __('The confirm password does not match.'));
                }

                $result = $this->checkCodeVerify($viewer->id, 'email_verify', $data['code'], $data['email']);
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
            'email.max' => __('The email must not be greater than 255 characters.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.unique' => __('The email has already been taken.'),
            'password.required' => __('The password is required.'),
            'password_confirmed.required' => __('The confirm password is required.'),
            'code.required' => __('The code is required.'),
        ];
    }
}
