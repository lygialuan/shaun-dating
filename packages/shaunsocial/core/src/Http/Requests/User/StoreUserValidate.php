<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PasswordValidation;
use Packages\ShaunSocial\Core\Validation\UserNameValidate;

class StoreUserValidate extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:64',
            'email' => 'required|string|max:255|email|unique:users,email',
            'user_name' => [
                'required',
                'string',
                'max:128',
                new UserNameValidate(),
                'unique:users,user_name'
            ]
        ];
        
        if ($this->get('id')) {
            $rules['email'] .= ','.$this->get('id');
            $rules['user_name'][4].= ','.$this->get('id');
        } else {
            $rules['password'] = ['required', 'string', new PasswordValidation()];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 64 characters.'),
            'email.required' => __('The email is required.'),
            'email.max' => __('The email must not be greater than 255 characters.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.unique' => __('The email has already been taken.'),
            'user_name.required' => __('The username is required.'),
            'user_name.unique' => __('The username has already been taken.'),
            'user_name.max' => __('The username must not be greater than 128 characters.'),
            'password.required' => __('The password is required.')
        ];
    }
}
