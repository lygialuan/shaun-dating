<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\Core\Validation\UserNameValidate;

class StoreGeneralSettingValidate extends PageValidate
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:64',
            'password' => [
                'required',
                new PasswordVerifyValidation(),
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
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 64 characters.'),
            'password.required' => __('The password is required.'),
            'user_name.required' => __('The username is required.'),
            'user_name.regex' => __('The username format is invalid.'),
            'user_name.unique' => __('The username has already been taken.'),
            'user_name.max' => __('The username must not be greater than 128 characters.'),
        ];
    }
}
