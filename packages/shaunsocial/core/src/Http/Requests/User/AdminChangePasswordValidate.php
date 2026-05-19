<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\AdminBaseFormAjaxRequest;
use Packages\ShaunSocial\Core\Validation\PasswordValidation;

class AdminChangePasswordValidate extends AdminBaseFormAjaxRequest
{
    public function rules()
    {
        return [
            'password' => ['required',new PasswordValidation()],
            'password_confirmed' => ['required'],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();

                if ($data['password'] != $data['password_confirmed']) {
                    return $validator->errors()->add('password_confirmed', __('The confirm password does not match.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.'),
            'password_confirmed.required' => __('The confirm password is required.')
        ];
    }
}
