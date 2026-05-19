<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AdminLoginValidate extends FormRequest
{
    protected $redirector = 'admin.auth.index';
    
    public function rules()
    {
        return [
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('The email is required.'),
            'password.required' => __('The password is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.exists' => __('The email or password was incorrect.'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }
}
