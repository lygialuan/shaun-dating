<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\Core\Validation\PasswordValidation;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class ChangePasswordValidate extends UserValidate
{
    public function rules()
    {
        return [
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
            'password_new' => ['required',new PasswordValidation()],
            'password_new_confirmed' => ['required'],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();

                if ($data['password_new'] != $data['password_new_confirmed']) {
                    return $validator->errors()->add('password_new_confirmed', __('The confirm password does not match.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.'),
            'password_new.required' => __('The new password is required.'),
            'password_new_confirmed.required' => __('The confirm password is required.')
        ];
    }
}
