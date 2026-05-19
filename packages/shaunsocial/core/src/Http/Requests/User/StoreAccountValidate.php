<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\Core\Validation\UserNameValidate;

class StoreAccountValidate extends UserValidate
{
    public function rules()
    {
        return [
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $email, $fail) {
                    if (checkEmailBan($email)) {
                        return $fail(__('The email has been banned.'));
                    }

                    $viewer = $this->user();
                    $user = User::findByField('email', $email);

                    if ($user && $user->id != $viewer->id) {
                        return $fail(__('The email has already been taken.'));
                    }
                },
            ],
            'user_name' => [
                'required',
                'max:128',
                new UserNameValidate(),
                function ($attribute, $userName, $fail) {
                    if (checkUsernameBan($userName)) {
                        return $fail(__('The username has been banned.'));
                    }
                    
                    $viewer = $this->user();
                    
                    $user = User::findByField('user_name', $userName);

                    if ($user && $user->id != $viewer->id) {
                        return $fail(__('The username has already been taken.'));
                    }
                },
            ]
        ];
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.'),
            'email.required' => __('The email is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.max' => __('The email must not be greater than 255 characters.'),
            'user_name.required' => __('The username is required.'),
            'user_name.regex' => __('The username format is invalid.'),
            'user_name.unique' => __('The username has already been taken.'),
            'user_name.max' => __('The username must not be greater than 128 characters.'),
        ];
    }
}
